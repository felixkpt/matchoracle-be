import useAxios from "@/hooks/useAxios"
import { useEffect, useState } from "react"
import Loader from "./Loader"
import { CompetitionGameSourceInterface } from "@/interfaces/FootballInterface"

interface Props {
    record: any
    hideClose?: boolean
}

const AddSource = ({ record, hideClose }: Props) => {

    const [localKey, setLocalKey] = useState(0);

    const [gameSources, setGameSources] = useState([])
    const [currentGameSources, setCurentGameSources] = useState<CompetitionGameSourceInterface[]>([])

    const { get } = useAxios()

    useEffect(() => {

        get('/admin/settings/picklists/game-sources?all=1').then((res: any) => res?.data && setGameSources(res.data))

    }, [])

    useEffect(() => {

        if (record) {
            setCurentGameSources(record.game_sources)
        }

    }, [record])

    return (
        <div>
            {
                gameSources.length > 0 ?

                    <table className="table" key={localKey}>
                        <tbody>
                            {
                                gameSources.map((item: any) => {
                                    let curr = currentGameSources.find((itm: any) => itm.id === item.id)

                                    if (curr) {
                                        curr = curr.pivot
                                    }

                                    return (
                                        <tr className="mb-3" key={item.id}>
                                            <th className="d-flex flex-column"><span>{item.name}</span><span className="fw-lighter">{item.url}</span></th>
                                            <td className="form-group">
                                                <label htmlFor={`${item.id}_source_uri_input`}>URI:</label>
                                                <input type="text" id={`${item.id}_source_uri_input`} name={`${item.id}_source_uri`} defaultValue={curr?.source_uri} className="form-control" />
                                            </td>
                                            <td className="form-group">
                                                <label htmlFor={`${item.id}_source_id_input`}>Source ID:</label>
                                                <input type="text" id={`${item.id}_source_id_input`} name={`${item.id}_source_id`} defaultValue={curr?.source_id} className="form-control" />
                                            </td>
                                            <td className="form-group">
                                                <label htmlFor={`${item.id}_subscription_expires_input`}>Subscription Expires:</label>
                                                <input type="datetime-local" id={`${item.id}_subscription_expires_input`} name={`${item.id}_subscription_expires`} defaultValue={curr?.subscription_expires} className="form-control" />
                                                <div className="form-check mt-2">
                                                    <input
                                                        className="form-check-input"
                                                        id={`${item.id}_subscription_expires_check_input`}
                                                        type='checkbox'
                                                        name={`${item.id}_subscription_expires_check_input`}
                                                        defaultChecked={curr?.subscription_expires === 'never'}
                                                    />
                                                    <label className="form-check-label" htmlFor={`${item.id}_subscription_expires_check_input`}>
                                                        Never
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    )
                                })
                            }
                        </tbody>
                    </table>
                    :
                    <Loader />
            }

            <div className="modal-footer gap-1">
                {
                    !hideClose &&
                    <button type="button" className="btn btn-secondary" onClick={() => setLocalKey(localKey + 1)} data-bs-dismiss="modal">Close</button>
                }
                <button type="submit" className="btn btn-primary">Submit</button>
            </div>
        </div>
    )
}

export default AddSource