<template>
  <footer class="page-footer">
    <div class="page-footer-grid">
      <div class="page-footer-grid__info">
        <div class="page-footer-info__message">
          <p class="page-footer-message__headline">
            This project is supported by the National
            Aeronautics and Space Administration (NASA) grants NEED NUMBERS HERE. Any
            opinions, findings, conclusions or recommendations expressed in this material are those
            of the author and do not necessarily reflect the views of NASA.
          </p>
        </div>

        <div class="page-footer-info__resource-list">
          <div
              v-bind:key="resource.path"
              v-for="resource in resourceList"
              class="page-footer-info__resource-list-item"
          >
            <PageFooterHyperlink :path="resource.path">
              {{ resource.label }}
            </PageFooterHyperlink>
          </div>
        </div>
      </div>
      <div class="page-footer-grid__brand">
        <div class="page-footer-brand">
          <img
              class="page-footer-brand__logo"
              src="/src/assets/images/PSI-Logo-transparent.png"
              alt="PSI Logo"
          />
          <PageFooterHyperlink path="https://psi.edu/">
            A product of the<br>
            Planetary Science Institute
          </PageFooterHyperlink>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import PageFooterHyperlink from "@/components/page-footer-hyperlink.vue";

const resourceList = [
  {
    path: "/data",
    label: "Data",
  },
  {
    path: "/privacy",
    label: "Privacy policy",
  },
  {
    path: "/team",
    label: "Meet the Team",
  },
];
</script>
