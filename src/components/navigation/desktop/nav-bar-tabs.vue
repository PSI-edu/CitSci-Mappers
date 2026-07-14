<template>
  <div class="nav-bar__tabs">
    <NavBarDropdown
        label="Do Science"
        :items="[
              { path: '/do_science/lunar-melt', label: 'Lunar Features' },
              { path: '/do_science/lunar-melt-flows', label: 'Lunar Flows'},
              { path: '/do_science/mars-mosaic', label: 'Mars Mosaics' },
          ]"
    />
      <NavBarDropdown
          label="Learn More"
          :items="[
              { path: 'https://mappers.psi.edu/learn/', label: 'Knowledge Base', external: true },
              { path: 'https://mappers.psi.edu/learn/lunar-melt/', label: 'Explore the Moon', external: true },
              { path: 'https://mappers.psi.edu/learn/mars-mosiacs/', label: 'Explore Mars', external: true },
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
