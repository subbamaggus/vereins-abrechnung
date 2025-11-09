const attributeApp = Vue.createApp({
  data() {
    return {
      attributes: [],
      selectedAttribute: null,
      error: null,
      success: false,
    };
  },
  methods: {
    async fetchAttributes() {
        try {
            const response = await fetch('api.php?method=get_attributes');
            if (!response.ok) {
                throw new Error('Could not fetch attributes');
            }
            this.attributes = await response.json();
        } catch (e) {
            this.error = e;
        }
    },
  },
  mounted() {
    this.fetchAttributes();
  },  
  template: `
    <div>
      <a href="index.php">Back to Overview</a>

      <h1>Manage Attributes</h1>
      

      <div style="margin-bottom: 10px;">
        <div v-for="group in attributes" :key="group.id" style="margin-bottom: 5px;">
          <strong>{{ group.name }}:</strong>
          <br/>
          <label v-for="attr in group.attribute" :key="attr.id" style="margin-right: 10px; margin-left: 5px;">
            <input type="text" v-model="attr.name" />
            <br/>
          </label>
          <a href="#">new attribute</a>
        </div>
        <a href="#">new attribute group</a>
      </div>

      <p v-if="error" style="color: red;">{{ error }}</p>
      <p v-if="success" style="color: green;">Entry added successfully!</p>
    </div>
  `
});
attributeApp.mount('#attribute-app');
