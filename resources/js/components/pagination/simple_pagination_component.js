
import EventHandler from "../../helpers/event_handler.js";
import AbstractComponent from "../abstract_component.js";
import UrlHelper from "../../helpers/url_helper.js";
import RequestHelper from "../../helpers/request_helper";
import __i from "../../vendor/repeat-toolkit/i18n.js";


export default class SimplePagination extends AbstractComponent{


    constructor(dom_element = null)
    {
        super();

        this.state = {
            'meta': null,
            'links': null,
            'initial_page': this.getAttribute('initial_page') ?? 1,
            "limit": parseInt(this.getAttribute("limit") ?? "20"),
            'collection': JSON.parse(this.getAttribute('collection') ?? "[]"),
            'search_value': this.getAttribute('search_value') ?? "",
            'route': this.getAttribute('route') ?? "",
            from_date: this.getAttribute('from_date') ?? null,
            to_date: this.getAttribute('to_date') ?? null,
        };

        EventHandler.subscribeToEvent('search-event', this);
        EventHandler.subscribeToEvent('orders-filters-changed', this);

    }

    handleEvent(event, data)
    {
        if(event == 'clear-search')
        {
            this.state.search_value = "";
            this.state.meta = null;
            this.state.links = null;
            this.fetch();
        }

        if(event == 'search-event')
        {
            this.state.search_value = data.term;

            if(this.state.search_value.length < 3 && this.state.search_value != "")
                return false;

            this.state.meta = null;
            this.state.links = null;
            this.state.append = 0;




            this.fetch();
        }

        if(event == 'orders-filters-changed')
        {
            this.state.meta = null;
            this.state.links = null;
            this.state.append = 0;
            this.state.from_date = data.from_date;
            this.state.to_date = data.to_date;
            this.state.paid_status = data.paid_status;



            this.fetch();
        }
    }

    render()
    {

    }

    fetch(link)
    {
       // console.log(link);

        let context = this;
      
        RequestHelper.makeRequest("POST", link ?? this.state.route, this.getFetchParams())
            .then(response => this.fetchCallback(context, response));
    }


    getFetchParams()
    {

    }

    fetchCallback(context, response)
    {


        context.state.collection = response.data;

        context.state.meta = response.meta;
        context.state.links = response.links;



        context.render();
    }


    connectedCallback()
    {

        console.log(this.state.meta);
        console.log('SimplePagination init');


    }


    getNextPageButton()
    {
        let next_button = `<button ${this.state.links != null && this.state.links.next != null ? "" : "disabled"}  data-href="${this.state.links != null ? this.state.links.next : ""}" class="page_btn w-100 btn btn-primary btn-lg"><span class="fa fa-arrow-right"></span></button>`;



        if(this.state.links != null && this.state.links.prev == null && this.state.links.next == null)
        {
            next_button = "";
        }

        return next_button;
    }

    getPreviousPageButton()
    {
        let prev_button = `<button ${this.state.links != null && this.state.links.prev != null ? "" : "disabled"}  data-href="${this.state.links != null ? this.state.links.prev : ""}" class="page_btn w-100 btn btn-primary btn-lg"><span class="fa fa-arrow-left"></span></button>`;


        if(this.state.links != null && this.state.links.prev == null && this.state.links.next == null)
        {
            prev_button = "";
        }



        return prev_button;
    }

    getNavigationButtons()
    {

        let first_page = 1;
        let current_page = this.state.meta.current_page;
        let last_page = this.state.meta.last_page;

        let pages = ``;

        let base_route = this.state.meta.path;

        for(let i = first_page; i <= last_page; i++)
        {
            pages += `<li class="page-item ${i == current_page ? "active" : ""}"><a class="page-link page_btn" data-href="${base_route + "?page=" + i}" href="javascript:void(0);">${i}</a></li>`;
        }

        let pagination = `

        <nav aria-label="Page navigation example">

  <ul class="pagination pagination_holder">
    <li class="page-item" ><button class="page-link page_btn" href="javascript:void(0);" ${this.state.links != null && this.state.links.prev != null ? "" : "disabled"}  data-href="${this.state.links != null ? this.state.links.prev : ""}">${__i("Prethodna")}</button></li>

     ${pages}
    <li class="page-item" ><button class="page-link page_btn" href="javascript:void(0);" ${this.state.links != null && this.state.links.next != null ? "" : "disabled"}  data-href="${this.state.links != null ? this.state.links.next : ""}">${__i("Sledeća")}</button></li>

  </ul>
      ${__i("Ukupno")}: ${this.state.meta.total}

</nav>

        `;

        return pagination;



        let context = this;
        return ` <div class="row pagination_holder">
                <div class="col">${context.getPreviousPageButton()}</div>
                <div class="col">${context.getNextPageButton()}</div>
            </div>`;
    }

    attachListeners()
    {
        let context = this;
        this.querySelectorAll('.page_btn').forEach(function (btn){
            btn.addEventListener('click', function (e){
                context.fetch(btn.getAttribute('data-href'));

                UrlHelper.setParam('page', btn.textContent);
            }) ;
        });
    }


}

customElements.define('simple-pagination', SimplePagination);
