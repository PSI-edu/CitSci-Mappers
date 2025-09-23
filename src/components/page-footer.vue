<template>
  <footer class="page-footer">
    <div class="page-footer-grid">
      <div class="page-footer-grid__info">
        <div class="page-footer-info__message">
          <img src="https://wm-web-assets.s3.us-east-2.amazonaws.com/NASA-Grantee.png"
               class="left" style="height: 70px; margin-right: 10px;">
          <p class="page-footer-message__headline">
            This project is supported by the National
            Aeronautics and Space Administration (NASA) grants 23-CSSFP23-0014 and NNH21ZDA001N-PDART. Any
            opinions, findings, conclusions or recommendations expressed in this material are those
            of the author and do not necessarily reflect the views of NASA.
          </p>
          <p>
            Learn more about NASA Citizen Science and find other projects to explore at
            <a href="https://science.nasa.gov/citizen-science">science.nasa.gov/citizen-science</a>. You can also
            check out other ways to get involved with NASA science at
            <a href="https://www.nasa.gov/get-involved/">nasa.gov/get-involved</a>.
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
