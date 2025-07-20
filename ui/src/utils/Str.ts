class Str {
    static slug(input: string | null | undefined) {
        if (!input) return ''

        const str = input
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9-]/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');

        return str;
    }

    static uriMethods(input: string): string {
        const str = input
            .trim()
            .replace(/\/|@/g, '_')
            .replace(/\|/g, '_');

        return str;
    }

    static before(subject: string, search: string): string {
        if (!subject) return subject

        return subject.split(search)[0];
    }

    static afterFirst(subject: string, search: string): string {
        const index = subject.indexOf(search);
        if (index === -1) {
            return ""; // Search string not found, return an empty string
        }
        return subject.substring(index + search.length);
    }

    static afterLast(subject: string, search: string): string {
        return subject.split(search).slice(-1)[0];
    }

    static title(subject: string, cases?: { [key: string]: string }): string {
        if (!subject) return subject;

        let strVal = '';
        const str = subject.replace(/_/g, ' ').split(' ');
        for (let chr = 0; chr < str.length; chr++) {
            let sub = str[chr];
            if (sub === 'id') sub = 'ID';

            if (cases && cases[sub]) {
                sub = cases[sub];
            }

            strVal += sub.substring(0, 1).toUpperCase() + sub.substring(1, sub.length) + ' ';
        }
        return strVal.trim();
    }

    static upper(subject: string): string {
        if (!subject) return subject

        return subject.toUpperCase()
    }

    static studly(subject: string): string {
        return subject
            .split(/[_\s]+/)
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join('');
    }

    static camel(subject: string): string {
        const words = subject.split(/[_\s]+/);
        const firstWord = words[0].toLowerCase();

        const camelCaseWords = words.slice(1).map((word) => {
            const capitalizedWord = word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
            return capitalizedWord;
        });

        return firstWord + camelCaseWords.join('');
    }

    static replace(subject: string, search: string, replacement: string): string {
        if (!subject) return subject;

        // Use the replace method to replace occurrences of search with the replacement string
        const result = subject.replace(new RegExp(search, 'g'), replacement);

        return result;
    }

    static contains(haystack: string, needle: string): boolean {
        return haystack.includes(needle);
    }

    static formatTime = (totalSeconds: number) => {
        const days = Math.floor(totalSeconds / 86400); // 86400 seconds in a day
        const hours = Math.floor((totalSeconds % 86400) / 3600); // 3600 seconds in an hour
        const minutes = Math.floor((totalSeconds % 3600) / 60); // 60 seconds in a minute
    
        let formattedTime = '';
    
        if (days > 0) {
            formattedTime += `${days} day${days > 1 ? 's' : ''} `;
        }
        if (hours > 0) {
            formattedTime += `${hours} hr${hours > 1 ? 's' : ''} `;
        }
        if (minutes > 0) {
            formattedTime += `${minutes} min${minutes > 1 ? 's' : ''}`;
        }
    
        return formattedTime.trim() || '0 min'; // Return '0 min' if no time is left
    };
}

export default Str