import { createClient } from 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm'

const SUPABASE_URL = 'https://wonkioqayeekzcuoepcb.supabase.co'
const SUPABASE_KEY = 'a_tua_chave_anon_aqui'

export const supabase = createClient(SUPABASE_URL, SUPABASE_KEY)