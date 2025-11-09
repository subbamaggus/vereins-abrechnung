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
      
        <div v-if="attributes.length" style="margin-bottom: 10px;">

            <div v-for="item in years" :key="item.id">
                <input type="radio" :id="'year-' + item.year" name="yearPicked" :value="item.year" v-model="currentYear" @change="clickYear(item.year)" >
                <label :for="'year-' + item.year">{{ item.year }}</label>
            </div>
            <div style="margin-bottom: 10px;">
                <strong>Filter by:</strong>
                <div v-for="group in attributes" :key="group.id" style="margin-bottom: 5px;">
                    <strong>{{ group.name }}:</strong>
                    <label v-for="attr in group.attribute" :key="attr.id" style="margin-right: 10px; margin-left: 5px;">
                        <input type="checkbox" :value="attr.id" v-model="selectedFilters"> {{ attr.name }}
                    </label>
                </div>
                <button @click="applyFilters">Apply Filters</button>
            </div>

        </div>

      <p v-if="error" style="color: red;">{{ error }}</p>
      <p v-if="success" style="color: green;">Entry added successfully!</p>
    </div>
  `
});
attributeApp.mount('#attibute-app');
