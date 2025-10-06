export default class DateHelper {
    static events = {}

    static formatDate(mysqlDate) {
        if (!mysqlDate) return '';

        const parts = mysqlDate.split('-');
        if (parts.length !== 3) return '';

        const [year, month, day] = parts;
        return `${day.padStart(2, '0')}.${month.padStart(2, '0')}.${year}`;
    }

    static getTodayMysqlDate() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
}
