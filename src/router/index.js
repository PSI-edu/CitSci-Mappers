import HomePage from "@/pages/home-page.vue";
import { authGuard } from "@auth0/auth0-vue";
import { createRouter, createWebHistory } from "vue-router";
import CallbackPage from "@/pages/callback-page.vue";

const NotFoundPage = () => import("@/pages/not-found-page.vue");
const ConsentPage = () => import("@/pages/consent-page.vue");
const ProfilePage = () => import("@/pages/profile-page.vue");
const ProtectedPage = () => import("@/pages/protected-page.vue");
const DataPage = () => import("@/pages/data-page.vue");
const PrivacyPage = () => import("@/pages/privacy.vue");
const TeamPage = () => import("@/pages/team.vue");
const MarsMosaicPage = () => import("@/pages/mars-mosaic-page.vue");
const MarsMosaicTutorialPage = () => import("@/pages/tutorials/mars-mosaic-tutorial-page.vue");
const LunarMeltPage = () => import("@/pages/lunar-melt-page.vue");
const LunarMeltTutorialPage = () => import("@/pages/tutorials/lunar-melt-tutorial-page.vue");
const MasterImagePage = () => import("@/pages/master-image-page.vue");

const routes = [
  {
    path: "/",
    name: "home",
    component: HomePage,
  },
  {
    path: "/consent",
    name: "consent",
    component: ConsentPage,
  },
  {
    path: "/profile",
    name: "profile",
    component: ProfilePage,
    beforeEnter: authGuard,
  },
  {
    path: "/protected",
    name: "protected",
    component: ProtectedPage,
    beforeEnter: authGuard,
  },
  {
    path: "/data",
    name: "data",
    component: DataPage,
  },
  {
    path: "/privacy",
    name: "privacy",
    component: PrivacyPage,
  },
  {
    path: "/team",
    name: "team",
    component: TeamPage,
  },
  {
    path: "/mars-mosaic",
    name: "mars-mosaic",
    component: MarsMosaicPage,
    beforeEnter: authGuard,
  },
  {
    path: "/tutorials/mars-mosaic-tutorial",
    name: "mars-mosaic-tutorial",
    component: MarsMosaicTutorialPage,
    beforeEnter: authGuard,
  },
  {
    path: "/lunar-melt",
    name: "lunar-melt",
    component: LunarMeltPage,
    beforeEnter: authGuard,
  },
  {
    path: "/tutorials/lunar-melt-tutorial",
    name: "lunar-melt-tutorial",
    component: LunarMeltTutorialPage,
    beforeEnter: authGuard,
  },
  {
    path: "/master-image",
    name: "master-image",
    component: MasterImagePage,
    beforeEnter: authGuard,
  },
  {
    path: "/callback",
    name: "callback",
    component: CallbackPage,
  },
  {
    path: "/:catchAll(.*)",
    name: "Not Found",
    component: NotFoundPage,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
