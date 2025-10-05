
import SelectInput from "./select_input.js";
import RequestHelper from "../../helpers/request_helper";


export default class FetchSelect extends SelectInput {


    constructor() {
        super();




        Object.assign(this.state, {
            'element_collection': JSON.parse(this.getAttribute('element_collection') ?? "[]"),

        });




    }

    fetchData(){



        RequestHelper.makeRequest("POST", this.state.fetch_url, this.state.fetch_data ?? {} ).then(response => {
            this.state.element_collection = response.data;
            this.state.element_collection[""] = __i("Nije odabrano");
            this.render();
        });

    }

    connectedCallback() {
        this.render();
        this.fetchData();
    }


}

customElements.define('fetch-select', FetchSelect);
