import { useState, useEffect } from 'react';
import { useNavigate, useParams, useLocation } from 'react-router-dom';
import FormatDate from '@/utils/FormatDate';

const useFromToDates = (base_uri?: string, append_date_in_uri?: boolean) => {
    const location = useLocation();
    const navigate = useNavigate();
    const params = useParams();

    const [baseUri, setBaseUri] = useState(base_uri)
    const [previousUrl, setPreviousUrl] = useState<string | null>(null)

    const [fromToDates, setFromToDates] = useState<Array<string | undefined>>([]);

    const dates_in_uri = typeof append_date_in_uri === 'boolean' ? append_date_in_uri : true

    useEffect(() => {

        let url = location.pathname
        setBaseUri(url ? `${url}` : base_uri)

        if (previousUrl !== location.pathname) {
            setPreviousUrl(location.pathname)
        }

        const { start_year, start_month, start_day, end_year, end_month, end_day } = params;

        if (start_year && start_month && start_day && end_year && end_month && end_day) {
            setFromToDates([
                FormatDate.YYYYMMDD(new Date(Number(start_year!), Number(start_month!) - 1, Number(start_day!))),
                FormatDate.YYYYMMDD(new Date(Number(end_year!), Number(end_month!) - 1, Number(end_day!)))
            ]);
        } else {
            const { year, month, day } = params;

            if (year && month && day) {
                setFromToDates([
                    FormatDate.YYYYMMDD(new Date(Number(year!), Number(month!) - 1, Number(day!))),
                    undefined
                ]);
            }
        }

    }, [location.pathname, location.search, location.hash]);

    useEffect(() => {
        let combinedDates = '';
        if (fromToDates[0]) {
            combinedDates = fromToDates[0];
            if (fromToDates[1]) {
                if (combinedDates != fromToDates[1]) {
                    combinedDates = `${combinedDates}/to/${fromToDates[1]}`;
                }

                const newUrl = `${base_uri}${dates_in_uri ? combinedDates : ''}` + (location.search + location.hash);
                navigate(newUrl);
            }
        } else if (fromToDates[0] !== undefined && fromToDates[1] !== undefined) {
            const newUrl = `${base_uri}` + (location.search + location.hash);
            navigate(newUrl);
        }

    }, [fromToDates, navigate, location.pathname, location.search, location.hash])



    return { fromToDates, setFromToDates, baseUri, setBaseUri, previousUrl };
};


export default useFromToDates;
