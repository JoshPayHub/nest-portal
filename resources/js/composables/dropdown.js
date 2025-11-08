// resources/js/composables/dropdown.js
import { ref, onMounted, onBeforeUnmount } from 'vue'

export function useDropdown() {
    // Keep track of which dropdown is open: null = none open
    const openDropdown = ref(null)

    // Outside click handler
    const handleClickOutside = (event) => {
        const dropdowns = document.querySelectorAll('.dropdown-wrapper')
        let clickedInside = false

        dropdowns.forEach(el => {
            if (el.contains(event.target)) clickedInside = true
        })

        if (!clickedInside) openDropdown.value = null
    }

    onMounted(() => window.addEventListener('click', handleClickOutside))
    onBeforeUnmount(() => window.removeEventListener('click', handleClickOutside))

    // Toggle dropdown function
    const toggleDropdown = (index) => {
        openDropdown.value = openDropdown.value === index ? null : index
    }

    return {
        openDropdown,
        toggleDropdown
    }
}
