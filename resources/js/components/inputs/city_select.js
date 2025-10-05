import AbstractComponent from "../abstract_component.js";
import FormInputComponent from "./form_input_component.js";
import NiceSelect from "nice-select2";
import Select2Input from "./select2_input.js";
import RequestHandler from "../../helpers/request_handler.js";
import {Ziggy} from "../../ziggy.js";
import {MotoSearchFiltersChangedEvent} from "../../events/moto_search_filters_change_filter.js";
import {MotorcycleBrandChanged} from "../../events/motorcyle_brand_changed.js";

export default class CitySelect extends Select2Input {


    constructor() {
        super();


        Object.assign(this.state, {
            'country_id': this.getAttribute('country_id') ?? 1,
            'element_type': 'select2',
            'element_name': this.getAttribute('element_name') ?? 'city',
            'element_options': JSON.parse(this.getAttribute('options') ?? '{}'),

        });




    }


    fetch()
    {
        let request_handler = new RequestHandler();

        request_handler.makeRequest('POST', route('zip_codes.get_all_from_params'), {'filters': {'filter__country_id__equal': this.state.country_id}, 'order_by': {'city': 'asc'}}).then(response => {

            if(response.success)
            {
                let cities = [];

                response.data.forEach(data => {
                    let obj = {"id": data['city'], 'name': data['city']};

                    cities.push(obj);

                });

                this.state.element_options = cities;
            }

            this.render();
        });
    }

    connectedCallback() {

        console.log('CitySelect connected');

        this.fetch();


    }


    attachListeners() {
        super.attachListeners();

    }


}

customElements.define('city-select', CitySelect);
