import Loader from "@/components/Loader"
import useAxios from "@/hooks/useAxios"
import { useEffect, useState } from "react"
import Pagination from "@/components/Pagination"
import TipsContent from "./TipsContent"
import InvestmentCard from "./InvestmentCard"

type Props = {
    uri: string
    type: string
    odds_name: string
    odds_name_print: string
}

const Multiples = ({ uri, type, odds_name, odds_name_print }: Props) => {

    const [page, setPage] = useState<number | string>(1);
    const [per_page, setPerPage] = useState<number | string>(5);
    const { data, get, loading } = useAxios()

    useEffect(() => {
        let localUri = `${uri}?type=${type}&page=${page}&per_page=${per_page}&multiples=1`
        get(localUri)
    }, [uri, page, per_page])

    return (
        <div>
            {
                data ?
                    <div className="card">
                        <div className="card-header">
                            <h5 className="d-flex gap-2 justify-content-between">Accumulators <span className="text-success">{data.total} betslips</span></h5>
                        </div>
                        <div className="card-body">
                            {
                                !loading || data ?

                                    <>
                                        {
                                            data.data.map((items: any) => {

                                                return (
                                                    <>
                                                        <TipsContent data={items.betslip} odds_name={odds_name} odds_name_print={odds_name_print} odds={items.odds} outcome={items.outcome} />
                                                    </>
                                                )
                                            })
                                        }
                                        <InvestmentCard investment={data.investment} />
                                    </>
                                    :
                                    <Loader />

                            }
                            <Pagination items={data} setPage={setPage} setPerPage={setPerPage} loading={loading} />
                        </div>

                    </div>
                    :
                    <>
                        {
                            loading ?
                                <Loader />
                                :
                                <div>Data Error</div>
                        }
                    </>

            }

        </div>
    )
}

export default Multiples