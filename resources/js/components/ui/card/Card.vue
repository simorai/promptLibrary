<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <div
    data-slot="card"
    :class="
      cn(
        'bg-card text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-[0_0_0_1px_rgba(74,222,128,0.18),0_0_26px_rgba(74,222,128,0.18),0_14px_34px_rgba(15,23,42,0.16)]',
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>
