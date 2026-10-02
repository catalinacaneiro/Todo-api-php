# Todo frontend

A small Svelte + Tailwind CSS interface for the existing PHP API. All task state
and fetch calls live in `src/App.svelte`. Vite handles development and builds.

## Run locally

Keep your PHP API running at `http://localhost:8000`. From the repository root:

```sh
cd frontend
npm install
npm run dev
```

Open the local URL printed by Vite (normally `http://localhost:5173`).
The development proxy forwards `/tasks` requests to the PHP server without
changing its paths or requiring backend CORS changes.

Click + to open the title form, submit to create a task, use the right-hand circle
to toggle completion, and use the small trash button to delete a task.

## Build

```sh
npm run build
```

Output goes into `dist/`. The proxy is for development only. A deployed frontend
needs its server to forward `/tasks` to the API.

No PHP files or database configuration are part of this frontend.
