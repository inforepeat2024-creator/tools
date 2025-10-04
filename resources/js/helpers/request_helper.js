

export default class RequestHelper{

    // Example POST method implementation:
    static async makeRequest(method = '', url = '', data = {}) {



        let serialized_data = data;


        if(data instanceof FormData)
        {
            // Default options are marked with *
            const response = await fetch(url, {
                method: method, // *GET, POST, PUT, DELETE, etc.
                mode: 'cors', // no-cors, *cors, same-origin
                cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
                credentials: 'same-origin', // include, *same-origin, omit
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With' : 'XMLHttpRequest'
                },
                redirect: 'follow', // manual, *follow, error
                referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
                body: data // body data type must match "Content-Type" header
            });
            return response.json(); // parses JSON response into native JavaScript objects
        }
        else
        {
            serialized_data = JSON.stringify(data);
            // Default options are marked with *
            const response = await fetch(url, {
                method: method, // *GET, POST, PUT, DELETE, etc.
                mode: 'cors', // no-cors, *cors, same-origin
                cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
                credentials: 'same-origin', // include, *same-origin, omit
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') != null ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : "-1",
                    'Content-Type': 'application/json',
                    'X-Requested-With' : 'XMLHttpRequest'
                    // 'Content-Type': 'application/x-www-form-urlencoded',
                },
                redirect: 'follow', // manual, *follow, error
                referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
                body: serialized_data // body data type must match "Content-Type" header
            });



            return response.json(); // parses JSON response into native JavaScript objects
        }



    }



}

