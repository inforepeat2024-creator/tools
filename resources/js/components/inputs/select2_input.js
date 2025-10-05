import AbstractComponent from "../abstract_component.js";
import FormInputComponent from "./form_input_component.js";
import NiceSelect from "nice-select2";
import Choices from "choices.js";
import EventHandler from "../../helpers/event_handler.js";
import {MotoSearchFiltersClear} from "../../events/moto_search_filters_clear.js";

export default class Select2Input extends FormInputComponent {


    select2_instance;

    constructor() {
        super();

        this.select2_instance = null;

        Object.assign(this.state, {
            'element_type': 'select2',
            'element_options': JSON.parse(this.getAttribute('options') ?? '[]'),

        });


        EventHandler.subscribeToEvent(MotoSearchFiltersClear.type, this);



    }

    handleEvent(event, data)
    {
        if(event === MotoSearchFiltersClear.type)
        {



            this.state.element_value = "";



            this.render();

        }
    }

    getStyle()
    {
        return `<style>
    .nice-select {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 40px; /* ili koliko ti treba */
        font-size: 1.2rem; /* veća slova */
        padding: 0 12px;
    }

    .nice-select .current {
        font-size: 1.2rem; /* uvećaj tekst */
        text-align: center;
        width: 100%;
    }

    .choices__item{
        color: #000000 !important;
        font-size:16px !important;
    }

    .choices__list--dropdown {
        color:black;
        width: 100%; /* 🟢 isto kao roditelj */
        left: 0;     /* poravnaj s leve strane */
        position: absolute; /* već je, ali za svaki slučaj */
        box-sizing: border-box;
        padding: 0 12px;
        z-index:5 !important;
    }

    .choices__inner{
    padding: 4.5px 7.5px 3.75px !important;
    }

    .choices[data-type*="select-one"] .choices__input {
        color: black !important;
    }

</style>`;
    }


    getOptions()
    {
        let options = ``;


        if(this.state.element_options)
        {
            this.state.element_options.forEach((option) => {






                options += `<option value="${option.id}"  ${option.id == this.state.element_value ? "selected" : ""}>${option.name}</option>`;

            });
        }





        return options;
    }

    renderHtml()
    {



        this.innerHTML = `


${this.getStyle()}

    <div class="form-group">
        ${this.getLabel()}
        <select
            name="${this.state.element_name}"
            ${this.renderRequired()}
            class="form-control w-100 ${this.state.element_class}"
        >
            <option value="" >--</option>
            ${this.getOptions()}
        </select>
    </div>

    `;
    }

    render() {



        let context = this;


        this.renderHtml();


        this.initSelect2();

        this.afterRender();
        this.attachListeners();

    }


    initSelect2()
    {


        let context = this;


        this.select2_instance = new Choices(this.querySelector('select'), {
            itemSelectText: __i("Klikni"),
            placeholderValue: context.state.element_placeholder,
            placeholder: true,
        });


    }


}

customElements.define('select2-input', Select2Input);
