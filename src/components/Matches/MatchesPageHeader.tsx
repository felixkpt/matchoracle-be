import FormatDate from "@/utils/FormatDate";
import { useEffect, useState } from "react";
import Flatpickr from "react-flatpickr";

type Props = {
    title?: string
    fromToDates: Array<Date | string | undefined>
    setFromToDates: React.Dispatch<React.SetStateAction<Array<Date | string | undefined>>>;
    className?: string
};

const MatchesPageHeader = ({ title, fromToDates, setFromToDates, className }: Props) => {

    const [key, setKey] = useState<number>(0)
    const [resetClasses, setresetClasses] = useState<string>('')

    function handleSetDate(selectedDates: Date[], resets = false) {

        if (selectedDates[1] || !selectedDates[0] && !selectedDates[1]) {
            const from_date = FormatDate.YYYYMMDD(selectedDates[0])
            const to_date = FormatDate.YYYYMMDD(selectedDates[1])
            setFromToDates([from_date, to_date])
        }

        if (resets) {
            setKey(key + 1)
        }
    }

    useEffect(() => {

        if (fromToDates[0]) {
            setresetClasses('btn btn-badge border ms-1 bg-success-subtle opacity-1 cursor-pointer')
        } else {
            setresetClasses('btn btn-badge border ms-1 bg-success-subtle opacity-0 cursor-default')
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
            <div className="col-md-12 px-0" key={key}>
                <div className={`row ${title ? 'justify-content-between' : 'justify-content-end'} `}>
                    {title && <h4 className="col-md-6">{title}</h4>}
                    {
                        typeof setFromToDates === 'function' &&
                        <div className="col-md-6">
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
                                    className={`${resetClasses ? resetClasses : 'btn btn-badge border ms-1 bg-success-subtle opacity-0'}`}>
                                    <small>Reset</small>
                                </button>
                            </div>
                        </div>
                    }
                </div>
            </div>
        </div>
    )
}

export default MatchesPageHeader