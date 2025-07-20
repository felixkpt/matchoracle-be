class FormatDate {

    static YYYYMMDD(date: Date) {
        if (!date) return ''
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}/${month}/${day}`;
    }

    static DDMMYY(date: Date | string, full: boolean = false) {
        if (!date) return

        if (typeof date === 'string') {
            date = new Date(date)
        }

        let year = date.getFullYear();
        year = full ? year : year % 100;
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${day}/${month}/${year}`;
    }

    static MMYY(date: Date | string, full = false) {
        if (!date) return;

        if (typeof date === 'string') {
            date = new Date(date);
        }

        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const month = monthNames[date.getMonth()];
        const year = full ? date.getFullYear() : String(date.getFullYear()).substr(-2);

        return `${month} ${year}`;
    }


    static DDMMYYYY(date: Date | string) {
        return this.DDMMYY(date, true)
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

    static toLocaleDateString(dateString: string): string {
        const options: Intl.DateTimeFormatOptions = {
            weekday: 'short', // "short", "long", or "narrow"
            month: 'short', // "numeric", "2-digit", "short", "long", or "narrow"
            day: 'numeric', // "numeric" or "2-digit"
            year: 'numeric', // "numeric" or "2-digit"
            hour: 'numeric', // "numeric" or "2-digit"
            minute: 'numeric', // "numeric" or "2-digit"
            hour12: true // boolean
        };

        const formattedDate = new Date(dateString).toLocaleDateString('en-US', options);

        return formattedDate;
    }

}

export default FormatDate