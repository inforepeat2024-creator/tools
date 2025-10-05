import AbstractComponent from "../abstract_component.js";
import FormInputComponent from "./form_input_component.js";
import SelectInput from "./select_input.js";
import RequestHandler from "../../helpers/request_handler.js";

export default class FetchSelect extends SelectInput {


    constructor() {
        super();




        Object.assign(this.state, {
            'element_collection': JSON.parse(this.getAttribute('element_collection') ?? "[]"),

        });




    }

    fetchData(){

        let request_handler = new RequestHandler();

        request_handler.makeRequest("POST", this.state.fetch_url, this.state.fetch_data ?? {} ).then(response => {
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
