<?php
// index.php — simple SPA shell
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Company Manager (CVR)</title>
  <link rel="stylesheet" href="assets/style.css" />
</head>
<body>
  <header>
    <h1>Company Manager</h1>
    <p>Add companies by CVR and sync details from CVRAPI.dk</p>
  </header>

  <main>
    <section class="card">
      <h2>Add company</h2>
      <form id="add-form">
        <label for="cvr">CVR number</label>
        <input id="cvr" name="cvr" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" placeholder="e.g. 26616409" required />
        <button type="submit">Add</button>
      </form>
      <p class="hint">CVR must be 8 digits.</p>
    </section>

    <section class="card">
      <div class="toolbar">
        <h2>Companies</h2>
        <div class="spacer"></div>
        <button id="sync-all" title="Fetch latest details for all">Synchronize all</button>
      </div>
      <div class="table-wrap">
        <table id="companies">
          <thead>
            <tr>
              <th>ID</th>
              <th>CVR</th>
              <th>Name</th>
              <th>Phone</th>
              <th>Email</th>
              <th>Address</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </section>
  </main>

  <footer>
    <small>Data source: <a href="https://cvrapi.dk/" target="_blank" rel="noopener noreferrer">CVRAPI.dk</a>. This demo uses SQLite and vanilla PHP.</small>
  </footer>

  <!-- Template row used by JS when rendering the table -->
  <template id="row-template">
    <tr>
      <td class="id"></td>
      <td class="cvr mono"></td>
      <td class="name"></td>
      <td class="phone"></td>
      <td class="email"></td>
      <td class="address"></td>
      <td class="actions">
        <button data-action="sync">Sync</button>
        <button data-action="delete" class="danger">Delete</button>
      </td>
    </tr>
  </template>

  <!-- Load frontend logic -->
  <script src="assets/app.js" defer></script>
</body>
</html>
