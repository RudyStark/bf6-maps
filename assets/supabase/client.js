import { createClient } from '@supabase/supabase-js';

function getMeta(name) {
    const el = document.querySelector(`meta[name="${name}"]`);
    return el ? el.getAttribute('content') : '';
}

const supabaseUrl = getMeta('supabase-url');
const supabaseAnon = getMeta('supabase-anon');

export const supabase = createClient(supabaseUrl, supabaseAnon, {
    auth: {
        persistSession: true, // garde la session dans localStorage
        autoRefreshToken: true,
    },
});
