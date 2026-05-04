import { createClient } from "@/utils/supabase/server";

/**
 * Page de exemplo Supabase SSR.
 * Crie uma tabela `todos (id uuid, name text)` no SQL Editor ou ajuste a query para uma tabela existente.
 */
export default async function Page() {
  const supabase = await createClient();

  const { data: todos, error } = await supabase.from("todos").select();

  if (error) {
    return (
      <main className="flex min-h-[50vh] flex-col gap-2 p-8">
        <h1 className="text-xl font-semibold">Supabase</h1>
        <p className="text-red-600 text-sm">{error.message}</p>
      </main>
    );
  }

  return (
    <main className="flex min-h-[50vh] flex-col gap-2 p-8">
      <h1 className="text-xl font-semibold">Todos</h1>
      <ul className="list-disc pl-5">
        {(todos ?? []).map((todo: { id: string | number; name: string }) => (
          <li key={todo.id}>{todo.name}</li>
        ))}
      </ul>
    </main>
  );
}
