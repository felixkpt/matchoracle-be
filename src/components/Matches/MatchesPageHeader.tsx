import FormatDate from "@/utils/FormatDate";
import { useState } from "react";
import Flatpickr from "react-flatpickr";

type Props = {
    title: string;
    fromToDates: any
    setFromToDates: any
};

const MatchesPageHeader = ({ title, fromToDates, setFromToDates }: Props) => {

    const [key, setKey] = useState<number>(0)

    function handleSetDate(selectedDates: Date[]) {
        let from_date = FormatDate.YYYYMMDD(selectedDates[0])
        let to_date = ''
        if (selectedDates[1]) {
            to_date = FormatDate.YYYYMMDD(selectedDates[1])
        }
        setFromToDates([from_date, to_date])
    }

    return (
        <div className='header-title shadow-sm p-2 rounded row justify-content-between'>
            <div className="col-md-8">
                <div className="d-flex justify-content-center justify-content-md-start">
                    <h3 className='heading'>{title}</h3>
                </div>
            </div>
            <div className="col-md-4" key={key}>
                {
                    typeof setFromToDates === 'function' &&
                    <div className="d-flex justify-content-center justify-content-md-end">
                        <Flatpickr
                            value={fromToDates}
                            data-mode="range"
                            data-date-format="Y-m-d"
                            onChange={(selectedDates: Date[]) => handleSetDate(selectedDates)}
                            placeholder='--- Pick a date ---'
                            className="text-center form-control w-auto cursor-pointer"
                            data-position="auto center"
                        />
                        {fromToDates[0] &&
                            <button onClick={() => {
                                handleSetDate([])
                                setKey(key + 1)
                            }}
                                className="btn btn-badge border ms-1 bg-success-subtle">
                                <small>Reset</small>
                            </button>
                        }
                    </div>
                }
            </div>
        </div>
    )
}

export default MatchesPageHeader