# Development Notes

## Project setup

- Started with a minimal project structure.
- Chose not to generate a complete architecture from the beginning.
- I want the structure to grow as functionality is added.

## Decisions

API planning: Decided to keep the Task model simple and only include the fields needed for the assignment. 
Planned four CRUD endpoints before implementing the database.

### PHP version before Symfony
I decided to first build the API with plain PHP and MySQL.
This will help me understand the API and database flow before
rebuilding it with Symfony and Doctrine.

## AI / Codex
- First time using Codex as a develoment assistant. 
- Used Codex as a development assistant.
- Changes are made in small steps.
- I review and understand changes before committing them.




## Task model & API design

I reviewed the initial API design with Codex. I decided to keep the API deliberately simple and avoid adding features that were not part of the requirements.  
I chose PATCH instead of PUT because tasks should support partial updates, for example changing only completed.  
I also decided that title is required, description is optional, completed defaults to false, and created_at is generated automatically.

## Database setup:
 Created the MySQL database manually in DBeaver and created the task table based on the data model I had planned.

## GET /tasks
- Implemented the first API endpoint.
- Reads the request method and path in index.php.
- Reuses the existing PDO connection.
- Fetches tasks from the task table and returns JSON.
- First tested with an empty table → [].
- Added a test task manually in DBeaver and verified that the API returned it correctly.
- Reviewed and understood the implementation before committing.



----------------------------------------------------------------------------------------------


## Checklist 

## Starta projektet + Git – skapa mappen/repo och en minimal grundstruktur. KLAR 

## Bestäm API:t och Task-modellen – t.ex. att en task har id, title, description, completed, created_at. KLAR 

## API-endpoints - 4 stycken KLAR
## GET / tasks - list tasks - 200 ok, JSON array KLAR - fre 2 okt 
POST / tasks - create a task - 201 Created task 
PATCH / Update selected fields - 200 ok 
DELETE / Delete a task - 204 no content, no response. 



## Skapa MySQL-databasen med SQL ← här gör du databasen. KLAR 30e sept kl 13.44 


Koppla PHP till MySQL med PDO.

Bygg GET /tasks.

Bygg POST /tasks.

Bygg PATCH /tasks/{id}.

Bygg DELETE /tasks/{id}.

Lägg till validering och felhantering.

Testa hela CRUD-flödet och städa upp.