# Laravel Performance Business API
...
## 🚀 Key Focus Areas
...
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
Using API Resources without eager loading can silently introduce the N+1 query problem, leading to inefficient database usage and scalability issues.
<br>

<fieldset style="background-color:darkgray">
    <legend> CONTEXT </legend>

The system exposes a Sales API endpoint, where each Sale is related to a Client.
Each SaleResource includes client information:

<i>app/Http/Resources/Api/V1/Sale/SaleResource.php</i>

```bash
'client' => [
    'id' => $this->client->id,
    'name' => $this->client->name,
],
```
</fieldset>
<br>
❌ <b>SCENARIO 1 - Lazy Loading (N + 1 Problem)</b>

- Implementation

<i>app/Services/SaleService.php</i>

```bash
return Sale::latest()->paginate(10);
```
<br>

- Behavior

When the SaleResource accesses: $this->client
Laravel resolves the relationship using lazy loading, executing additional queries per item.
<br>
- Query Breakdown
    
1 query → fetch sales
N queries → fetch clients (one per sale)
<br>
- Problem

This approach introduces a linear growth in database queries (O(n)), which results in:
- unnecessary database load
- poor scalability under high data volume
- hidden performance issues inside serialization layer
<hr>
✅ <b> SCENARIO 2 — Optimized Solution (Eager Loading)</b>

- Implementation
    
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
<br>
- Behavior

All required relationships are loaded in advance using eager loading, avoiding additional queries during serialization.
<br>
- Query Breakdown

1 query → fetch sales
1 query → fetch clients
<br>
- Result

This approach reduces relationship query complexity from:   O(n) → O(1)
ensuring predictable performance regardless of dataset size.
<hr>
🔍 <b>KEY INSIGHT</b>

While execution time differences may be minimal in small datasets, the real impact of eager loading is not latency reduction, but query scalability and database load control.

</details>

---

