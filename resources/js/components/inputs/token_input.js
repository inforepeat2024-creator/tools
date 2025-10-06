




export default class TokenInput extends HTMLElement {



    constructor() {
        super();

        this.state = {
            csrf_token: this.getAttribute('csrf_token') ?? (document.querySelector('meta[name="csrf-token"]') != null ? document.querySelector('meta[name="csrf-token"]').content : ""),
        };


    }

    attributeChangedCallback(name, oldValue, newValue) {
        //this.render();
    }

    handleEvent(name, data)
    {








    }


    connectedCallback() {
        console.log('TokenInput init');
        this.render();
    }





    render() {

        this.innerHTML = `


        <input name="_token" value="${this.state.csrf_token}" type="hidden">


        `;


    }












}

customElements.define('token-input', TokenInput);
