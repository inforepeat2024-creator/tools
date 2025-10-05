
import Select2Input from "./select2_input.js";
import RequestHelper from "../../helpers/request_helper";


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


        RequestHelper.makeRequest('POST', route('zip_codes.get_all_from_params'), {'filters': {'filter__country_id__equal': this.state.country_id}, 'order_by': {'city': 'asc'}}).then(response => {

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
