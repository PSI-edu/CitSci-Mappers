<template>
  <div class="nav-bar__tabs">
    <NavBarTab path="https://mappers.psi.edu/learn" label="Learn" external />
    <NavBarDropdown
        label="Do Science"
        :items="[
              { path: '/do_science/lunar-melt', label: 'Lunar Melt: Craters, Boulders, Rocks' },
              { path: '/do_science/lunar-melt-flows', label: 'Lunar Melt: Flows, Faults, & More' },
              { path: '/do_science/mars-mosaic', label: 'Mars Mosaics' },
          ]"
    />
    <template v-if="isAuthenticated">
      <NavBarTab path="/profile" label="Profile" />
    </template>
  </div>
</template>

<script setup>
import NavBarTab from "@/components/navigation/desktop/nav-bar-tab.vue";
import { useAuth0 } from "@auth0/auth0-vue";
import NavBarDropdown from "@/components/navigation/desktop/nav-bar-dropdown.vue";

const { isAuthenticated } = useAuth0();
</script>
