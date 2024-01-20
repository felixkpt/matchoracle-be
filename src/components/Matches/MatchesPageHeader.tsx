import FormatDate from "@/utils/FormatDate";
import { useEffect, useState } from "react";
import Flatpickr from "react-flatpickr";

type Props = {
    title: string;
    fromToDates: any
    setFromToDates: any
    className?: string
};

const MatchesPageHeader = ({ title, fromToDates, setFromToDates, className }: Props) => {

    const [key, setKey] = useState<number>(0)
    const [resetStyles, setResetStyles] = useState<object>({})

    function handleSetDate(selectedDates: Date[], resets = false) {
        let from_date = FormatDate.YYYYMMDD(selectedDates[0])
        let to_date = ''
        if (selectedDates[1]) {
            to_date = FormatDate.YYYYMMDD(selectedDates[1])
        }
        setFromToDates([from_date, to_date])

        if (resets) {
            setKey(key + 1)
        }
    }

    useEffect(() => {

        if (fromToDates[0]) {
            setResetStyles({ opacity: 1 })
        } else {
            setResetStyles({ opacity: 0, cursor: 'default' })
        }

    }, [fromToDates])

    const [classNames, setClassNames] = useState('header-title shadow-sm p-2 rounded row justify-content-between')

    useEffect(() => {

        if (className) {
            setClassNames((exists) => {

                const curr = exists.split(' ')
                const add = className.split(' ')

                return curr.join(' ') + ' ' + add.join(' ')

            })
        }

    }, [className])

    return (
        <div className={classNames}>
            <div className="col-md-8">
                <div className="d-flex justify-content-center justify-content-md-start">
                    <h3 className='heading'>{title}</h3>
                </div>
            </div>
            <div className="col-md-4 px-0" key={key}>
                {
                    typeof setFromToDates === 'function' &&
                    <div className="d-flex justify-content-center justify-content-md-end">
                        <Flatpickr
                            value={fromToDates}
                            data-mode="range"
                            data-date-format="Y-m-d"
                            onChange={(selectedDates: Date[]) => handleSetDate(selectedDates)}
                            placeholder='--- Pick a date ---'
                            className="text-center form-control cursor-pointer"
                            data-position="auto center"
                        />
                        <button onClick={() => handleSetDate([])}
                            className="btn btn-badge border ms-1 bg-success-subtle" style={resetStyles}>
                            <small>Reset</small>
                        </button>
                    </div>
                }
            </div>
        </div>
    )
}

export default MatchesPageHeader