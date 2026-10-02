<script>
  import { onMount, tick } from 'svelte';

  let tasks = $state([]);
  let loading = $state(true);
  let error = $state('');
  let adding = $state(false);
  let saving = $state(false);
  let busyIds = $state([]);
  let title = $state('');
  let titleInput = $state();

  const date = new Intl.DateTimeFormat('en', {
    weekday: 'long', month: 'long', day: 'numeric',
  }).format(new Date());

  // PDO may return completed as 0/1 or "0"/"1".
  function normalize(task) {
    return { ...task, completed: Number(task.completed) === 1 };
  }

  async function request(path, method = 'GET', data) {
    const response = await fetch(path, {
      method,
      ...(data === undefined ? {} : {
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      }),
    });

    if (!response.ok) {
      const body = await response.json().catch(() => null);
      throw new Error(body?.error || 'Something went wrong. Please try again.');
    }

    // A successful DELETE has no JSON body.
    return response.status === 204 ? null : response.json();
  }

  onMount(() => {
    async function loadTasks() {
      try {
        tasks = (await request('/tasks')).map(normalize);
      } catch (cause) {
        error = cause.message;
      } finally {
        loading = false;
      }
    }
    loadTasks();
  });

  async function showAddForm() {
    adding = !adding;
    if (adding) {
      await tick();
      titleInput?.focus();
    }
  }

  async function createTask(event) {
    event.preventDefault();
    if (!title.trim() || saving) return;
    saving = true;
    error = '';
    try {
      const task = await request('/tasks', 'POST', { title: title.trim() });
      tasks = [...tasks, normalize(task)];
      title = '';
      await tick();
      titleInput?.focus();
    } catch (cause) {
      error = cause.message;
    } finally {
      saving = false;
    }
  }

  async function updateTask(task, method) {
    if (busyIds.includes(task.id)) return;
    busyIds = [...busyIds, task.id];
    error = '';
    try {
      const result = await request(`/tasks/${task.id}`, method,
        method === 'PATCH' ? { completed: !task.completed } : undefined);
      tasks = method === 'DELETE'
        ? tasks.filter((item) => item.id !== task.id)
        : tasks.map((item) => item.id === task.id ? normalize(result) : item);
    } catch (cause) {
      error = cause.message;
    } finally {
      busyIds = busyIds.filter((id) => id !== task.id);
    }
  }
</script>

<main class="mx-auto min-h-screen w-full max-w-xl px-7 pb-20 pt-20 sm:px-10 sm:pt-32">
  <header class="mb-14 flex items-center justify-between gap-4">
    <div>
      <h1 class="font-serif text-[clamp(2.25rem,7vw,3.25rem)] leading-tight tracking-tight">Today’s Tasks</h1>
      <p class="mt-3 text-xs tracking-wide text-[#817486]">{date}</p>
    </div>
    <button
      class="flex size-11 shrink-0 items-center justify-center rounded-full border border-[#c9bacf] text-[#786482] transition-colors hover:bg-[#ebe1ef]"
      aria-label={adding ? 'Close add task form' : 'Add a task'}
      aria-expanded={adding}
      aria-controls="add-task-form"
      onclick={showAddForm}
    >
      <svg aria-hidden="true" viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.3">
        <path d={adding ? 'M6 6l12 12M6 18L18 6' : 'M12 5v14M5 12h14'} />
      </svg>
    </button>
  </header>

  {#if adding}
    <form id="add-task-form" class="mb-8 flex items-end gap-3 border-b border-[#dcd1e0] pb-4" onsubmit={createTask}>
      <div class="min-w-0 flex-1">
        <label for="task-title" class="sr-only">Task title</label>
        <input
          bind:this={titleInput}
          bind:value={title}
          id="task-title"
          placeholder="What would you like to do?"
          autocomplete="off"
          required
          readonly={saving}
          class="w-full bg-transparent py-3 text-sm placeholder:text-[#93849a]"
        />
      </div>
      <button type="submit" disabled={saving || !title.trim()} class="min-h-11 px-2 text-sm text-[#786482] hover:text-[#514957]">
        {saving ? 'Adding…' : 'Add'}
      </button>
    </form>
  {/if}

  {#if error}
    <p role="alert" class="mb-6 text-sm leading-relaxed text-[#964b64]">{error}</p>
  {/if}

  {#if loading}
    <p role="status" class="py-8 text-sm text-[#817486]">Loading your tasks…</p>
  {:else if tasks.length === 0 && !error}
    <p role="status" class="py-8 text-sm leading-relaxed text-[#817486]">A little room for what matters.<br />Tap + to add your first task.</p>
  {:else}
    <ul aria-label="Tasks" class="divide-y divide-[#ded4e2]">
      {#each tasks as task (task.id)}
        <li class="flex min-h-20 items-center gap-2 py-4">
          <span class="min-w-0 flex-1 break-words pr-3 text-[15px] leading-relaxed {task.completed ? 'text-[#9a8e9f] line-through decoration-[#b6a9bb]' : ''}">{task.title}</span>
          <button
            class="flex size-11 shrink-0 items-center justify-center rounded-full text-[#a393ab] hover:text-[#964b64]"
            aria-label={`Delete ${task.title}`}
            disabled={busyIds.includes(task.id)}
            onclick={() => updateTask(task, 'DELETE')}
          >
            <svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 11v5M14 11v5" />
            </svg>
          </button>
          <button
            class="flex size-11 shrink-0 items-center justify-center rounded-full"
            aria-label={`Mark ${task.title} as ${task.completed ? 'uncompleted' : 'completed'}`}
            aria-pressed={task.completed}
            disabled={busyIds.includes(task.id)}
            onclick={() => updateTask(task, 'PATCH')}
          >
            <span class="flex size-6 items-center justify-center rounded-full border {task.completed ? 'border-[#b29abe] bg-[#b29abe] text-white' : 'border-[#c4b4cc] hover:bg-[#ebe1ef]'}">
              {#if task.completed}
                <svg aria-hidden="true" class="size-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 8 3 3 7-7" /></svg>
              {/if}
            </span>
          </button>
        </li>
      {/each}
    </ul>
  {/if}
</main>
