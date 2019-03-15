
import Vue from 'vue'
import VueRouter from 'vue-router'


Vue.use(VueRouter);

// components
import communicate from './components/chat/communicate'
import list from './components/chat/list'

const routes = [
    {path: '/chat/:id', component: communicate},
    {path: '/messages', component: list},
];

const router = new VueRouter({
    routes,
    mode: 'history'
});

export default router;


