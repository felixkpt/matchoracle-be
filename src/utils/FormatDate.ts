class FormatDate {

    static YYYYMMDD(date: Date) {
        if (!date) return
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}/${month}/${day}`;
    }

    static DDMMYY(date: Date | string) {
        if (!date) return

        if (typeof date === 'string') {
            date = new Date(date)
        }

        const year = date.getFullYear() % 100;
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${day}/${month}/${year}`;
    }
    static HHMM(date: Date | string) {
        if (!date) return

        if (typeof date === 'string') {
            date = new Date(date)
        }

        const hour = String(date.getHours()).padStart(2, '0');
        const minute = String(date.getHours()).padStart(2, '0');

        return `${hour}:${minute}`;
    }
}

export default FormatDate