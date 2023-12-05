import useAxios from "@/hooks/useAxios"
import { TeamInterface } from "@/interfaces/FootballInterface"
import { Icon } from "@iconify/react/dist/iconify.js";
import { useEffect, useState } from "react"
import AsyncSelect from 'react-select/async';
import { DataInterface } from "@/interfaces/UncategorizedInterfaces";
import { PropsValue } from "react-select";
import Loader from "../Loader";

type Props = {
    record: DataInterface | undefined
}

const UpdateCoach = ({ record }: Props) => {

    const { get, loading } = useAxios()
    const [team, setTeam] = useState<TeamInterface>()

    useEffect(() => {

        if (record) {
            setTeam(record.record)
        }

    }, [record])

    const loadOptions = async (q: string) => {

        if (team) {

            const currentValue = team?.coach_contract?.coach;

            const { data: fetchedOptions } = await get(`/admin/teams/coaches?all=1&q=${q}`);

            setSelected(fetchedOptions.find((itm: any) => itm.id === currentValue.id));

            // Include the existing record's option in fetchedOptions if not already present
            if (currentValue && !fetchedOptions.some((option: any) => option.id === currentValue.id)) {
                fetchedOptions.push(currentValue);
            }

            return fetchedOptions
        }

    }

    const [selected, setSelected] = useState<PropsValue<object> | undefined>();

    return (
        <div key={team?.id}>
            {
                team ?
                    <div>
                        <div className="form-group mb-2">
                            <label htmlFor="coachID">Coach</label>
                            <AsyncSelect
                                id="coachID"
                                className="form-control"
                                name={`coach_id`}
                                value={selected}
                                defaultOptions
                                loadOptions={(q: any) => loadOptions(q)}
                                getOptionValue={(option: any) => `${option['id']}`}
                                getOptionLabel={(option: any) => `${option['name']}`}
                            />
                        </div>
                        <div className="form-group mb-2">
                            <label htmlFor="coachStart">Start</label>
                            <input type="date" name="start" className="form-control" id="coachStart" defaultValue={team.coach_contract?.start} />
                        </div>
                        <div className="form-group mb-2">
                            <label htmlFor="coachUntil">Until</label>
                            <input type="date" name="until" className="form-control" id="coachUntil" defaultValue={team.coach_contract?.until} />
                        </div>
                        <div className="form-group">
                            <button type="submit" className="btn btn-info btn-raised submit-btn"><Icon icon="save"></Icon> Submit</button>
                        </div>
                    </div>
                    :
                    <Loader />
            }

        </div>
    )
}

export default UpdateCoach