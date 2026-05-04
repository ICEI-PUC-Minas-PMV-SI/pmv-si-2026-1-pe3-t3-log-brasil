import { createServerClient } from "@supabase/ssr";
import { cookies } from "next/headers";

function getEnvOrThrow(): [string, string] {
  const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL;
  const supabaseKey = process.env.NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY;
  if (!supabaseUrl || !supabaseKey) {
    throw new Error(
      "Defina NEXT_PUBLIC_SUPABASE_URL e NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY em .env.local"
    );
  }
  return [supabaseUrl, supabaseKey];
}

/**
 * Cliente Supabase no servidor (Server Components / Route Handlers).
 * Compartilha cookies com o browser via @supabase/ssr.
 */
export async function createClient() {
  const [supabaseUrl, supabaseKey] = getEnvOrThrow();
  const cookieStore = await cookies();

  return createServerClient(supabaseUrl, supabaseKey, {
    cookies: {
      getAll() {
        return cookieStore.getAll();
      },
      setAll(cookiesToSet) {
        try {
          cookiesToSet.forEach(({ name, value, options }) =>
            cookieStore.set(name, value, options)
          );
        } catch {
          /* setAll pode ser chamado de Server Component: sessão será atualizada pelo middleware. */
        }
      },
    },
  });
}
