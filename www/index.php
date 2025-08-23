<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vue Liste</title>
  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
</head>
<body>

<div id="app"></div>

<script>
  const app = Vue.createApp({
    setup() {
      // Zustand für die Daten, den Ladezustand und eventuelle Fehler
      const data = Vue.ref([]);
      const loading = Vue.ref(true);
      const error = Vue.ref(null);

      // Funktion zum Abrufen der Daten
      const fetchData = async () => {
        try {
          // Die URL der JSON-Daten
          const jsonUrl = 'http://localhost/v-a/www/api.php';
          const response = await fetch(jsonUrl);

          // Prüfen, ob die Antwort erfolgreich war
          if (!response.ok) {
            throw new Error(`HTTP Fehler! Status: ${response.status}`);
          }
          const result = await response.json();
          data.value = result;
        } catch (e) {
          error.value = e;
        } finally {
          loading.value = false;
        }
      };

      // Daten beim Erstellen der Komponente abrufen
      Vue.onMounted(fetchData);

      // Gib die Zustände und Daten an das Template zurück
      return {
        data,
        loading,
        error
      };
    },
    template: `
      <!-- Zeigt "Lädt..." an, solange die Daten geladen werden -->
      <div v-if="loading">Lädt...</div>

      <!-- Zeigt eine einfache Fehlermeldung an, wenn ein Fehler auftritt -->
      <div v-else-if="error">Fehler: {{ error.message }}</div>

      <!-- Zeigt die Liste an, wenn die Daten erfolgreich geladen wurden -->
      <div v-else>
        <h1>Posts aus JSON-URL</h1>
        <div>
          <div v-for="item in data" :key="item.id" style="margin-bottom: 15px; border: 1px solid #ccc; padding: 10px;">
            {{ item.name }} : {{ item.value }}
          </div>
        </div>
      </div>
    `
  });

  app.mount('#app');
</script>

</body>
</html>
