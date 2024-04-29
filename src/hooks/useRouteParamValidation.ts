import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';

const useRouteParamValidation = () => {
    const { year, month, date } = useParams();
    const [errorsState, setErrorsState] = useState(1);

    useEffect(() => {
        let failed = false;

        if (year && (!/^\d{4}$/.test(year) || parseInt(year) < 1800 || parseInt(year) > 2100)) {
            failed = true;
        }

        if (month && (!/^\d{1,2}$/.test(month) || parseInt(month) > 12)) {
            failed = true;
        }

        if (date && (!/^\d{1,2}$/.test(date) || parseInt(date) > 31)) {
            failed = true;
        }

        if (failed) {
            setErrorsState(2);
        } else {
            setErrorsState(0);
        }
    }, [year, month, date]);

    return errorsState;
};

export default useRouteParamValidation;
