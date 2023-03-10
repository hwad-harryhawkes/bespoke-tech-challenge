require('./bootstrap');

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => require(`./Pages/${name}.vue`),
    setup({ el, app, props, plugin }) {
        return createApp({ render: () => h(app, props) })
            .use(plugin)
            .mixin({ methods: { route, // new js method to search an array by key value pairs
            searchFilter(array, search, keys) {
                var filtered;
                return array.filter(item => {
                    return keys.some(
                        key =>
                            (filtered = String(item[key])
                                .toLowerCase()
                                .includes(search.toLowerCase()))
                    );
                });
            } },
                
            })
            .mount(el);
    },
});

InertiaProgress.init({ color: '#4B5563' });
