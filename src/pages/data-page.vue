<template>
  <PageLayout>
    <div class="content-layout">
      <h1 id="page-title" class="content__title">Data Policy</h1>
      <div class="content__body">
        <p id="page-description">
          This page will provide access to downloadable data for each of our projects once they are completed.
        </p>
        <br/>
        <h3>How we use your submitted data</h3>

        <div class="text-blurb">
          <p>
            <img class="icon" src="https://cosmoquest.s3.us-east-1.amazonaws.com/data/mosaics/Mars.png" alt="Mars Icon" />
           <strong>Mars Mosaics:</strong> Our science team has a new algorithm to merge images from the Mars
            Reconnaissance Orbiter (MRO) Context Camera (CTX). You are helping us understand where this algorithm
            works well and where it does not. Improved mosaics will allow researchers to better understand the
            geology of Mars, and to identify interesting locations for future exploration. Specifically, your work
            will help us understand regions where images overlap. This research will be used by a team led by
            Dr. Stuart Robbins at the Southwest Research Institute (SwRI) in Boulder, Colorado, the team who
            developed the algorithm we're verifying.
          </p>
        </div>
        <div class="clear"></div>

        <div class="text-blurb">
          <p>
            <img class="icon" src="https://moon-mappers.s3.us-east-2.amazonaws.com/Moon.png" alt="Moon Icon" />
           <strong>Lunar Melt:</strong> Our science team is working to understand the geology in and around impact
            craters with significant Lunar impact melt. The images come from the Lunar Reconnaissance Orbiter (LRO) and its
            Narrow Angle Camera (NAC). You are helping us understand where the melt is and how it relates to newer
            craters, and all the faults and fractures in the area. Improved maps will allow researchers to better
            understand the where future exploration should occur, and how the Moon's crust has changed over time. This
            data may be used to train machine learning algorithms used by researchers to accelerate mapping. Initial
            research will be used by a team led by Dr. Kirby Runyon at the Planetary Science Institute. A complete
            catalogue of mapped geological features will be made available to the public, with the publishable names
            (where permitted by each volunteer)
            of everyone who mapped each region will be associated with that region's map. If something particularly
            interesting
            is found, we will reach out to you to see if you would like to be a collaborator on any resulting papers.
        </p>

        </div>
        <div class="clear"></div>
        <div class="text-blurb">
          <p>
            <img class="icon" src="https://wm-web-assets.s3.us-east-2.amazonaws.com/Rocket.png" alt="Rocket Icon" />
           <strong>General Interactions:</strong> How you use this site is up to you. Map for a few minutes or a
            few hours. You can map a few images or a few thousand. You can come back tomorrow, next week, or next
            year. What you decide to do is actually super interesting to us, and we may perform research on how people
            interact with this site. We will not share your data with anyone else, and we will never name names unless
            you give us permission to do so. Any research we do on your behaviors will be governed by human subject
            research protocols, and overseen by a research review board.
          </p>
        </div>
      </div>

      <p class="italic">
        If you are a researcher interested in becoming a collaborator, please contact Dr. Pamela L. Gay at
        <a class="underline" href="mailto:plg@psi.edu">plg@psi.edu</a>.
      </p>

    </div>
  </PageLayout>
</template>

<script setup>
import PageLayout from "@/components/page-layout.vue";

</script>
