export default class EventHandler {
    static events = {}

    static subscribeToEvent(event, elem) {
        if(!EventHandler.events[event]) {
            EventHandler.events[event] = []
        }
        EventHandler.events[event].push(elem)
    }

    static unsubscribeFromEvent(event, elem)
    {
        let array = EventHandler.events[event];

        let new_array = [];

        for(var key in array)
        {
            let current = array[key];

            if(elem !== current)
            {
                new_array.push(current);
            }

        }




        EventHandler.events[event] = new_array;
    }

    static triggerEvent(event, params) {
        if(!EventHandler.events[event]) return;
        EventHandler.events[event].forEach(elem => {
            if(elem.handleEvent) {
                elem.handleEvent(event, params)
            }
        })
    }
}
