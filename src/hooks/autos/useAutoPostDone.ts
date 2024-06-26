import { useEffect, useState } from "react";
import { subscribe, unsubscribe } from "../../utils/events";


interface ResultsInterface {
    data: any,
    message: string | undefined,
    status: number | undefined
}
interface EventType extends ResultsInterface {
    id: string
}

const useAutoPostDone = () => {

    const [event, setEvent] = useState<EventType>()

    useEffect(() => {

        unsubscribe('autoPostDone', handleAutoPostDone);
        subscribe('autoPostDone', handleAutoPostDone);

        return () => {
            unsubscribe('autoPostDone', handleAutoPostDone);
        };

    }, [])

    const handleAutoPostDone = (resp: any) => {
     
        if (resp.detail) {
            const elementId = resp.detail?.elementId;
            const results: ResultsInterface = resp.detail?.results
            setEvent({ ...results, id: elementId })
        }
    };

    return { event }
}

export default useAutoPostDone