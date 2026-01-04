export default class DateHelper {
    static events = {}

    static formatDate(mysqlDate) {
        if (!mysqlDate) return '';

        const parts = mysqlDate.split('-');
        if (parts.length !== 3) return '';

        const [year, month, day] = parts;
        return `${day.padStart(2, '0')}.${month.padStart(2, '0')}.${year}`;
    }

}
