export default class NumberHelper {



    static toCommas(number)
    {

        return new Intl.NumberFormat('de-DE', {minimumFractionDigits: 2}).format(
            number,
        )

    }

    static delay(callback, ms) {
        var timer = 0;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                callback.apply(context, args);
            }, ms || 0);
        };
    }

    static toDots(number)
    {

        try {
            if(typeof number == 'undefined')
                return number;

            let formatted = number.replace('.', "");
            formatted = formatted.replace(',', ".");

            return parseFloat(formatted);
        }
        catch (e)
        {
            return number;
        }



    }





}
