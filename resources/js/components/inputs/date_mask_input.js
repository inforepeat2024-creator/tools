import TextInput from "./text_input.js";

export default class DateMaskInput extends TextInput {


    constructor() {
        super();

        Object.assign(this.state, {'inputmode': 'numeric'});


    }

    attachListeners()
    {


        super.attachListeners();

        var selector = this.querySelector('input');

        var im = new Inputmask("99.99.9999");
        im.mask(selector);





    }


}

customElements.define('date-mask-input', DateMaskInput);
