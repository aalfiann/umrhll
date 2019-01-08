import HomePage from './pages/home.vue';
import AboutPage from './pages/about.vue';
import TOSPage from './pages/tos.vue';
import FormPage from './pages/form.vue';
import RegisterPage from './pages/register.vue';
import RegisterAgentPage from './pages/register-agent.vue';
import MyProfilePage from './pages/my-profile.vue';
import DataJamaahPage from './pages/data-jamaah.vue';
import DataSaldoPage from './pages/data-saldo.vue';
import LoginPage from './pages/login.vue';
import DynamicRoutePage from './pages/dynamic-route.vue';
import UnderConstructionPage from './pages/under-construction.vue';

import PanelLeftPage from './pages/panel-left.vue';
import PanelRightPage from './pages/panel-right.vue';

export default [
  {
    path: '/',
    component: HomePage,
  },
  {
    path: '/login/',
    component: LoginPage,
  },
  {
    path: '/panel-left/',
    component: PanelLeftPage,
  },
  {
    path: '/panel-right/',
    component: PanelRightPage,
  },
  {
    path: '/about/',
    component: AboutPage,
  },
  {
    path: '/tos/',
    component: TOSPage,
  },
  {
    path: '/register/',
    component: RegisterPage,
  },
  {
    path: '/register-agent/',
    component: RegisterAgentPage,
  },
  {
    path: '/my-profile/',
    component: MyProfilePage,
  },
  {
    path: '/data-jamaah/',
    component: DataJamaahPage,
  },
  {
    path: '/data-saldo/',
    component: DataSaldoPage,
  },
  {
    path: '/form/',
    component: FormPage,
  },
  {
    path: '/dynamic-route/blog/:blogId/post/:postId/',
    component: DynamicRoutePage,
  },
  {
    path: '(.*)',
    component: UnderConstructionPage,
  },
];
