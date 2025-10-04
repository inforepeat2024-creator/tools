import routesPayload from '@/routes.gen.json';
import { makeRouter } from '../vendor/repeat-toolkit/route-lite.js';

const { route, url } = makeRouter(routesPayload);
window.route = route; // global
window.url = url;

export { route, url }; // i ESM export