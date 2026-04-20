# Laravel Performance Business API
...
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


## DEMONSTRATION SCENARIOS (D.S)

### D.S_1

<details>
<summary>N + 1 QUERY PROBLEM + EAGER LOADING OPTIMIZATION</summary> 
<br>
<b> Using API Resources without eager loading can silently introduce the N+1 query problem, leading to inefficient database usage and scalability issues.</b>

#### CONTEXT 

- The system exposes a Sales API endpoint, where each Sale is related to a Client.
- Each SaleResource includes client information:

<i>app/Http/Resources/Api/V1/Sale/SaleResource.php</i>

```bash
'client' => [
    'id' => $this->client->id,
    'name' => $this->client->name,
],

```

<hr>

#### ❌ SCENARIO 1 - Lazy Loading (N + 1 Problem)

Implementation

<i>app/Services/SaleService.php</i>

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
    **unnecessary database load**
    **poor scalability under high data volume**
    **hidden performance issues inside serialization layer**

<hr>

#### ✅ SCENARIO 2 — Optimized Solution (Eager Loading)

Implementation 

<i>app/Models/Sale.php</i>

```bash
public function client()
{
    return $this->belongsTo(Client::class);
}
```

<i>app/Services/SaleService.php</i>

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

<hr>

#### DEBUGBAR EVIDENCE

- Lazy Test
![Lazy Loading Debugbar](screenshots/lazy-test.png)
<i>Repeated queries to the clients table confirm the N+1 issue caused by lazy loading.</i>

- Eager Test
![Eager Loading Debugbar](screenshots/eager-test.png)
<i>As shown in Debugbar, query count remains constant regardless of dataset size, improving scalability and reducing unnecessary database load.</i>

<hr>

#### KEY INSIGHT

While execution time differences may be minimal in small datasets, the real impact of eager loading is not latency reduction, but query scalability and database load control.

</details>

---

### D.S_2 

<details>

<summary>Aggregate Query Optimization (SUM + Loop vs Single SQL Update)</summary> 

<br>

#### 📌 CONTEXT

- We are going to use as example the class database/seeders/DatabaseSeeder.php.
- Each Client stores a total_spent field, representing the total amount of all related sales.
- This value needs to be recalculated after generating seed data for benchmark scenarios.
- The relationship is:

```bash
Client → hasMany → Sales;
```
<i>The goal is to update total_spent efficiently for all clients.</i>

</hr>

#### ❌ SCENARIO 1 - Aggregate Query Inside Loop

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
<i>This creates an aggregate query per client.</i>

Query Breakdown

- 1 query → fetch all clients
- N queries → calculate SUM per client
- N queries → update each client

Problem

- This introduces an N+1 pattern in aggregate operations, resulting in:
    **excessive database round trips**
    **poor scalability**
    **slower seed execution**
    **unnecessary load for recalculated fields**

<hr>

#### ✅ SCENARIO 2 - Single SQL Update with Subquery

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

#### Key Insight

Even aggregate operations like SUM() can create N+1-style performance problems when executed inside loops.
Performance optimization is not only about relationships (with()), but also about how aggregate calculations are executed



</details>

---

### D.S_3 

<details>

<summary>Secure API Authentication with Laravel Sanctum</summary> 

<br>

#### 📌 TOKEN-BASED AUTHENTICATION

Authentication is implemented using Laravel Sanctum’s token-based system.

**REQUEST LIFECYCLE**

[^1]:A client sends a request to a protected endpoint like: 

```bash
GET /api/clients
Authorization: Bearer {token}
```

[^2]: The application entry point is *public/index.php*

[^3]: The application container is initialized by *bootstrap/app.php*> and core services are loaded. 

> Laravel boots the framework with service providers and middleware registration.

[^4]: The request gets at *routes/api.php* where Laravel checks the API route definitions.

> Protected routes are grouped using:

```bash
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('clients', ClientController::class);
});
```
> At this point, Laravel knows that the request must pass through Sanctum authentication before reaching the controller.

[^5]: The midleware auth:sanctum validate:
> if a Bearer token exists
> if the token is valid
> if the token belongs to an authenticated user
> if the token still has permission to access the resource
*If validation fails: 401 Unauthorized is returned immediately. The controller is never executed.*

[^6]: Controller Execution. Only authenticated requests reach the business logic:
> ClientController, SalesController, ProductController, SupplierController...
> This keeps authentication separated from domain logic, improving maintainability and system design.

**Why This Matters**

- Instead of validating authentication manually inside controllers, the project uses Laravel’s middleware pipeline to enforce security at the framework level.

- This provides:
> Centralized access control, cleaner controllers, better scalability, safer route protection, stronger architectural consistency.

Login 
- API tokens instead of session cookies
- After successful login, the system generates a personal access token using: 'createToken()'
- This token is returned to the client and must be included in subsequent requests as a Bearer token.
<i>app/Http/Controllers/Api/V1/Auth/AuthController.php</i>

```bash
public function login(Request $request)
{
    // Validations with Illuminate\Support\Facades\Auth;
    
    ...
    $user = $request->user();
    $token = $user->createToken('api-token')->plainTextToken;
    return response()->json([
            'user' => $user,
            'token' => $token
        ]);
}
```



Centralized Middleware Protection (auth:sanctum)

- All sensitive routes are grouped under the auth:sanctum middleware
- Only requests with valid tokens can access business logic endpoints




</details>





---