import HomePage from "@/pages/home-page.vue";
import { authGuard } from "@auth0/auth0-vue";
import { createRouter, createWebHistory } from "vue-router";
import CallbackPage from "@/pages/callback-page.vue";

const NotFoundPage = () => import("@/pages/not-found-page.vue");
const ProfilePage = () => import("@/pages/profile-page.vue");
const ProtectedPage = () => import("@/pages/protected-page.vue");
const DataPage = () => import("@/pages/data-page.vue");
const PrivacyPage = () => import("@/pages/privacy.vue");
const TeamPage = () => import("@/pages/team.vue");

const routes = [
  {
    path: "/",
    name: "home",
    component: HomePage,
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
