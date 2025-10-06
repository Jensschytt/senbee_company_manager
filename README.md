# Company Manager (CVR)

This is a simple web application to manage Danish companies using their **CVR number**.  
The project was created as part of a coding assignment and demonstrates the use of:

- **Frontend:** Vanilla HTML, CSS, and JavaScript (AJAX)
- **Backend:** PHP 8 + SQLite
- **API integration:** [CVRAPI.dk](https://cvrapi.dk)
- **Database:** SQLite (stores CVR numbers and synchronized company data)

---

## Features
- Add a company using its CVR number (must be 8 digits)
- Display a list of all added companies
- Synchronize company details (name, phone, email, address) from CVRAPI.dk
- Delete companies from the list
- "Synchronize all" button to update all companies at once

---

## Installation and Usage
1. Clone the repository:
   
   git clone https://github.com/Jensschytt/senbee_company_manager.git
   cd senbee_company_manager
   
3. Start local PHP server from the rootproject:   
   php -S 127.0.0.1:8080


## Potential Errors with the Database

Open PHP/php.ini

verify that all these extensions are enabled:
   - extension=pdo_sqlite
   -  extension=sqlite3
   -  extension=curl

Veryify that the extensions folder is set correctly:
   -  extension_dir = "ext"

Download the SSl CA Certificate Bundle: 
   - https://curl.se/docs/caextract.html

and save it as:
C:\PHP\extras\cacert.pem



