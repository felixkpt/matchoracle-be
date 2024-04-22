import Loader from "@/components/Loader"
import useAxios from "@/hooks/useAxios"
import { useEffect, useState } from "react"
import Pagination from "@/components/Pagination"
import TipsContent from "./TipsContent"
import InvestmentCard from "./InvestmentCard"

type Props = {
    uri: string
    type: string
}

const Singles = ({ uri, type}: Props) => {

    const [page, setPage] = useState<number | string>(1);
    const [per_page, setPerPage] = useState<number | string>(35);
    const { data, get, loading } = useAxios()

    useEffect(() => {
        let localUri = `${uri}${uri.includes('?') ? '&' : '?'}type=${type}&page=${page}&per_page=${per_page}`
        get(localUri)
    }, [uri, type, page, per_page])

    return (
        <div>
            <div className="card">
                <div className="card-header">
                    <h5 className="d-flex gap-2 justify-content-between">Accumulators <span className="text-success">{data?.total || 0} betslips</span></h5>
                </div>
                <div className="card-body">
                    {
                        data ?
                            <div>
                                {
                                    !loading || data ?

                                        <>
                                            {
                                                data.data.map((items: any, key: number) => {

                                                    return (
                                                        <div key={key}>
                                                            <TipsContent data={items} />
                                                        </div>
                                                    )
                                                })
                                            }
                                            <InvestmentCard investment={data.investment} />
                                        </>
                                        :
                                        <Loader />

                                }
                                <Pagination items={data} setPage={setPage} setPerPage={setPerPage} loading={loading} breakpoint='lg' />
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

            </div>

        </div>
    )
}

export default Singles