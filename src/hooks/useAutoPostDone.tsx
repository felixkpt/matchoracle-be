import { useEffect, useState } from "react";
import { subscribe, unsubscribe } from "../utils/events";

interface EventType {
    id: string
    response: { results: any, message: string, tableId: string }
    status: 'success' | 'failure'
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
            const { elementId, response } = resp.detail;
            const status = (response?.status == undefined || response?.status == 200 || response?.status == 201) ? 'success' : 'failure'
            setEvent({ id: elementId, response, status })
        }
    };

    return { event }
}

export default useAutoPostDone