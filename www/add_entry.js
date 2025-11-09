const addEntryApp = Vue.createApp({
  data() {
    return {
      newEntry: {
        name: '',
        value: '',
        date: new Date().toISOString().slice(0, 10),
        myimage: null,
      },
      error: null,
      success: false,
    };
  },
  methods: {
    async storeEntry() {
      this.error = null;
      this.success = false;
      const formData = new FormData();
      formData.append('name', this.newEntry.name);
      formData.append('value', this.newEntry.value);
      formData.append('date', this.newEntry.date);
      if (this.newEntry.myimage) {
        formData.append('myimage', this.newEntry.myimage);
      }

      try {
        const response = await fetch('api.php?method=store_entry', {
          method: 'POST',
          body: formData,
        });

        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.error || 'Failed to add entry');
        }

        const result = await response.json();
        if (result.success) {
          this.success = true;
          window.location.href = 'index.php';
        } else {
          throw new Error('Failed to add entry');
        }
      } catch (e) {
        this.error = e.message;
      }
    },
    handleFileUpload(event) {
      this.newEntry.myimage = event.target.files[0];
    },
  },
  template: `
    <div>
      <h2>Add New Entry</h2>
      <form @submit.prevent="storeEntry">
        <label>Betrag<br>
          <input type="number" step="0.01" v-model="newEntry.value">
        </label>
        <br>
        <label>Datum<br>
          <input type="date" v-model="newEntry.date">
        </label>
        <br>
        <label>Bezeichnung<br>
          <input type="text" v-model="newEntry.name">
        </label>
        <br>
        <label>Bild<br>
          <input type="file" @change="handleFileUpload" ref="fileInput">
        </label>
        <br>
        <button type="submit">speichern</button>
      </form>
      <p v-if="error" style="color: red;">{{ error }}</p>
      <p v-if="success" style="color: green;">Entry added successfully!</p>
      <a href="index.php">Back to Overview</a>
    </div>
  `
});
addEntryApp.mount('#add-entry-app');
