import './bootstrap.js';
import './styles/app.scss';
import './supabase/client';

import { registerReactControllerComponents } from '@symfony/ux-react';
registerReactControllerComponents(
    require.context('./react/controllers', true, /\.(j|t)sx?$/)
);

console.log('Encore + React prêts ✅');
