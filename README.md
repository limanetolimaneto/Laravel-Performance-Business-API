# Laravel Performance Business API

A high-performance backend system built with Laravel, designed to demonstrate advanced backend engineering concepts including best practices using Sanctum authentication, query optimization, queue processing, and scalable API architecture.

This project simulates a real-world business management system with a focus on performance, clean architecture, and asynchronous processing.

It is designed as a portfolio piece targeting backend and Laravel developer roles on platforms such as Upwork.

---

## D.S (Demonstration Scenario)

<details>
    <summary> <b> D.S 1 - N + 1 Query problem and Eager loading optimization </b> </summary>
<br>

**Using API Resources without eager loading can silently introduce the N+1 query problem, leading to inefficient database usage and scalability issues.**

<details>
    <summary> <b> ➡️ Lets use Sales API endpoint as example </b> </summary>
<br>

The system exposes a Sales API endpoint, where each Sale is related to a Client.

Each SaleResource includes client information:

*app/Http/Resources/Api/V1/Sale/SaleResource.php*

```bash
'client' => [
    'id' => $this->client->id,
    'name' => $this->client->name,
],

```
</details>

<br>

<details>
    <summary> <b> ❌ SCENARIO 1 - Lazy Loading (N + 1 Problem) </b> </summary>
<br>
Implementation

*app/Services/SaleService.php*

```bash
return Sale::latest()->paginate(10);
```

Behavior

- When the SaleResource accesses: $this->client
- Laravel resolves the relationship using lazy loading, executing additional queries per item.

Query Breakdown
    
- 1 query → fetch sales
- N queries → fetch clients (one per sale)

Problem

- This approach introduces a linear growth in database queries (O(n)), which results in:
    - unnecessary database load;
    - poor scalability under high data volume;
    - hidden performance issues inside serialization layer;
</details>

<br>

<details>
    <summary> <b> ✅ SCENARIO 2 - Optimized Solution (Eager Loading) </b> </summary>
<br>

Implementation 

*app/Models/Sale.php*

```bash
public function client()
{
    return $this->belongsTo(Client::class);
}
```

*app/Services/SaleService.php*

```bash
return Sale::with('client')->latest()->paginate(10);
```

Behavior

- All required relationships are loaded in advance using eager loading, avoiding additional queries during serialization.

Query Breakdown

- 1 query → fetch sales
- 1 query → fetch clients

Result

- This approach reduces relationship query complexity from:   O(n) → O(1)
ensuring predictable performance regardless of dataset size.

</details>

<br>

<details>
    <summary> <b> ➡️ Debugbar evidence - Screenshots </b> </summary>
<br>

- Lazy Test

![Lazy Loading Debugbar](screenshots/lazy-test.png)

*Repeated queries to the clients table confirm the N+1 issue caused by lazy loading.*

- Eager Test

![Eager Loading Debugbar](screenshots/eager-test.png)

*As shown in Debugbar, query count remains constant regardless of dataset size, improving scalability and reducing unnecessary database load.*

</details>

<br>

<details>
    <summary> <b> ➡️ Key insight <b> </summary>

While execution time differences may be minimal in small datasets, the real impact of eager loading is not latency reduction, but query scalability and database load control.

</details>

</details>

---

<details>
    <summary> <b> D.S 2 - Aggregate Query Optimization (SUM + Loop vs Single SQL Update) </b> </summary>
<br>


<details>
    <summary> <b> Lets use database/seeders/DatabaseSeeder.php as example: </b> </summary>
<br>
Each Client stores a total_spent field, representing the total amount of all related sales.
This value needs to be recalculated after generating seed data for benchmark scenarios.

- The relationship is:

```bash
Client → hasMany → Sales;
```
*The goal is to update total_spent efficiently for all clients.*

</details>

<br>

<details>
    <summary> <b> ❌ SCENARIO 1 - Aggregate Query Inside Loop </b> </summary>
<br>

Implementation

```bash
Client::all()->each(function ($client) {
    $client->update([
        'total_spent' => $client->sales()->sum('total_amount')
    ]);
});
```

Behavior

- For each client, Laravel executes:

```sql
SELECT SUM(total_amount)
FROM sales
WHERE client_id = ?
```
*This creates an aggregate query per client.*

Query Breakdown

- 1 query → fetch all clients
- N queries → calculate SUM per client
- N queries → update each client

Problem

- This introduces an N+1 pattern in aggregate operations, resulting in:
    - excessive database round trips;
    - poor scalability;
    - slower seed execution;
    - unnecessary load for recalculated fields;

</details>

<br>

<details>
    <summary> <b> ✅ SCENARIO 2 - Single SQL Update with Subquery </b> </summary>
<br>

Implementation

```sql
Client::query()->update([
    'total_spent' => DB::raw("(
        SELECT COALESCE(SUM(total_amount), 0)
        FROM sales
        WHERE sales.client_id = clients.id
    )")
]);
```

Behavior

- The database performs the full aggregation internally using a single SQL statement.
- No per-client loop is required.

Query Breakdown
- 1 query → update all clients

Result

- This approach reduces query complexity from: O(n) → O(1) and significantly improves scalability for large datasets.

</details>

<details>
    <summary> <b> Key Insight </b> </summary>
<br>

Even aggregate operations like SUM() can create N+1-style performance problems when executed inside loops.
Performance optimization is not only about relationships (with()), but also about how aggregate calculations are executed

</details>

</details>

---

<details>
    <summary> <b> D.S 3 Secure API Authentication with Laravel Sanctum </b> </summary>
<br>

**This project is a modular Business API composed of independent domains such as Clients, Sales and others**
> Because these modules expose protected business operations, authentication must happen before any controller logic is executed.

<br>

<details>
    <summary> <b> 📌 Understanding how a request travels inside Laravel </b> </summary>
<br>

When a client sends a request to a protected endpoint such as /api/clients

1. Entry Point (public/index.php) and Application Bootstrap — bootstrap/app.php
    - Every HTTP request starts in public/index.php, the main entry point of the Laravel application.
    - Here, the framework is bootstrapped and the application lifecycle begins.
    - Laravel initializes the service container, loads service providers, and registers middleware.
    - At this stage, authentication services such as Sanctum are prepared to participate in the request pipeline.

2. Route Resolution — routes/api.php
    - Laravel checks the API route definitions and determines whether the requested endpoint requires authentication.
    - Protected routes are grouped using middleware(auth:sanctum).
    - At this moment, Laravel knows the request must be validated by Sanctum before reaching the controller.

3. Authentication Layer — auth:sanctum
    - This is where Sanctum enters the request lifecycle.
    - The middleware validates:
        - if a Bearer token exists
        - if the token is valid
        - if the token belongs to an authenticated user
        - if the token is still authorized to access the resource
    - Laravel Sanctum supports this token-based flow for API authentication by issuing personal access tokens through methods like createToken().
    - If validation fails, Laravel immediately returns '401 Unauthorized' the controller is never executed.

4. Controller Execution
    - Only authenticated requests reach the business layer: ClientController, SalesController and others.
    - This keeps authentication separated from domain logic, improving maintainability and preserving clean architecture principles.

**Why This Design Matters**

- Instead of validating authentication manually inside controllers, this project uses Laravel’s middleware pipeline to enforce security at the framework level.
- This provides:
    - centralized access control;
    - cleaner controllers;
    - stateless API security;
    - easier scalability;
    - stronger architectural consistency;

</details>

<br>

<details>
    <summary><b> 📌 How Sanctum authenticates requests </b></summary>
<br>

Authentication starts during the login process.

When valid credentials are submitted, Laravel authenticates the user using:

-   *app/Http/Controllers/Api/V1/Auth/AuthController.php*
    ```bash
        public function login(Request $request)
        {
            ...
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }
            $user = $request->user();
            $token = $user->createToken('api-token')->plainTextToken;
            ...
        }
    ```
    - At this point, Laravel Sanctum generates a Personal Access Token associated with the authenticated user
    - This token is returned to the client and must be included in all subsequent requests using: Authorization: Bearer {token}

**What happens next**

When the client accesses a protected route such as GET api/clients
-   *routes/api.php*
    ```bash
        ...
        Route::middleware('auth:sanctum')->group(function () {
            Route::apiResource('clients', ClientController::class);
        ...
    ``` 
    - Laravel checks the Bearer token through: auth:sanctum
        - If the token is valid, the request reaches the controller.
        - If not **401 Unauthorized** is returned immediately.

</details>

<br>

<details>
    <summary> <b> 📌 How long a token lasts </b> </summary>
<br>

The sanctum token expiration can be centrally managed through config/sanctum.php
    
-   *config/sanctum.php*
    ```bash
    'expiration' => null,
    ```
    - The value null can be replced by the number of minutes until an issued token will be considered expired.

    - ⚠️ Without expiration configuration, Sanctum tokens remain valid indefinitely unless explicitly revoked.

        - When a user logs in, they receive a token:
            ```Json
            {
                "token": "abc123..."
            }
            ```
        - The request continues to work with the same token for days, weeks, or even months.
            ```bash
                Authorization: Bearer abc123...
            ```    
        - This token could be accidentally leaked;
        - The user's laptop could be stolen;
        > the risk of this token being used for malicious purposes is much higher if it never expires.
        
        ❌ The token never expires:
        *config/sanctum.php*
        ```bash
            'expiration' => null,
        ```
        
        ✅ The token lasts 24 hours:
        *config/sanctum.php*
        ```php
            'expiration' => 1440, 
        ```

        **POSTMAN TEST EVIDENCE**
        - Request with valid token

        ![Postman Test](screenshots/valid-token-test.png)
        
        *Token created at: 16:47:53*

        - Http Request with expired token (expiration => 1)
        
        ![Postman Test](screenshots/expired-token-test.png)
        
        *Protected endpoint requested at: 16:49:03*

</details>

<br>

<details>
    <summary> <b> 📌 How to invalidate a token </b> </summary>
<br>

The logout operations revoke tokens directly from the database.

-   *app/Http/Controllers/Api/V1/Auth/AuthController.php*
    ```bash
        ...
        public function logout(Request $request)
        {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'message' => 'Logged out'
            ]);
        }
        ...
    ```

</details>

</details>




---














## 🚀 Key Focus Areas
- Database performance optimization (N+1 problem solving, eager loading, query tuning)
- Intelligent use of Eloquent, Query Builder, and Raw SQL
## ⚙️ Core Features
### 🔐 Authentication
...
### 📊 Business Modules
...
### ⚡ Performance Engineering
...
### 📬 Async Processing
...
### 📄 Reporting System
...
## 🧠 Architectural Highlights
...
## 🛠 Tech Stack
...
## 🎯 Goal
...
