  <script>
    (function () {
      var b = document.body;
      b.classList.add('sidebar-bisa-susut');
      b.setAttribute('data-sidebar-kunci', @json($kunciSidebar));
      b.setAttribute('data-sidebar-id', @json($idSidebar));
      try {
        if (window.localStorage.getItem(@json($kunciSidebar)) === 'mini') b.classList.add('sidebar-mini');
      } catch (e) {  }
    })();
  </script>
