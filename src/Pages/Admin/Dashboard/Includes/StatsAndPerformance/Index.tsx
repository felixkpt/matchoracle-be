import { Icon } from '@iconify/react/dist/iconify.js'
import { NavLink } from 'react-router-dom'
import DetailedMatchesInfo from '../DetailedMatchesInfo'
import Loader from '@/components/Loader'
import { DashboardStatsInterface } from '@/interfaces/FootballInterface'
import DashJobLogsCard from '../DashJobLogsCard'
import useAxios from '@/hooks/useAxios'
import { useEffect, useState } from 'react'

type Props = {
}

const Index = ({ }: Props) => {

    const { get, loading, errors } = useAxios();
    const [stats, setStats] = useState<DashboardStatsInterface | null>(null);

    useEffect(() => {
        getStats()
    }, [])

    async function getStats() {
        get(`admin/advanced-stats`).then((results: any) => {
            if (results) {
                setStats(results)
            }
        })
    }

    return (
        <div className="row mb-4 align-items-start">
            <h5>Statistics & Performace</h5>
            <div className="col-xxl-8">
                <div className='row'>
                    <div className="col-12">
                        <div className="row">
                            <div className="col-lg-6 mb-4">
                                <NavLink to={`/admin/matches`} className={'link-unstyled'}>
                                    <div className="card shadow">
                                        <div className="card-header bg-secondary text-white">
                                            <h5 className='d-flex align-items-center gap-1'>
                                                <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                                <span>Matches detailed info</span>
                                            </h5>
                                        </div>
                                        <div className="card-body text-center">
                                            {
                                                stats?.advanced_matches ?
                                                    <DetailedMatchesInfo stats={stats.advanced_matches} />
                                                    :
                                                    <Loader />
                                            }
                                        </div>
                                    </div>
                                </NavLink>
                            </div>
                            <div className="col-lg-6 mb-4">
                                <NavLink to={`/admin/settings/system/predictions-performance`} className={'link-unstyled'}>
                                    <div className="card shadow">
                                        <div className="card-header bg-secondary text-white">
                                            <h5 className='d-flex align-items-center gap-1'>
                                                <Icon width={'2rem'} icon={`${'mdi:chart-line-variant'}`} />
                                                <span>Predictions performace overview</span>
                                            </h5>
                                        </div>
                                        <div className="card-body text-center">
                                        </div>
                                    </div>
                                </NavLink>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div className="col-xxl-4">
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nemo vitae labore beatae, quaerat vero, adipisci nobis suscipit tempora quam voluptas, similique quas velit maiores corrupti aut debitis itaque magni aperiam repudiandae repellat expedita. Maiores, earum? Vero iste doloribus iure assumenda in ipsum dolorem quam possimus, temporibus magni repudiandae ratione aliquam iusto eius? Corporis expedita repellat sit recusandae tenetur magnam optio at aliquam ipsam quod officiis facere adipisci iste laborum deserunt hic porro, inventore quidem! Ea delectus at dicta dignissimos magnam possimus expedita exercitationem nihil, eius unde ullam, repellendus dolorem similique consequatur vel doloremque, nemo quibusdam temporibus porro ab nesciunt id repudiandae. Soluta illo dolore qui hic eius necessitatibus recusandae animi quae voluptates fugiat quam laudantium minus beatae illum optio et consequatur tenetur asperiores rerum, non repudiandae? Perferendis corporis, incidunt accusantium doloremque minus exercitationem, hic amet tempore cum molestias ad quis cupiditate labore? Necessitatibus accusantium beatae aperiam animi exercitationem eaque, quasi nemo culpa numquam ratione distinctio nobis quam? Explicabo, distinctio ratione? Reiciendis dolore amet commodi id sapiente voluptates unde tempore repellendus odit cupiditate, quae perferendis esse, dolor corrupti necessitatibus sint ipsam iste inventore velit officiis alias harum. Quasi impedit quod tempore recusandae voluptatem adipisci quisquam consequuntur error maxime, deleniti est saepe fugit nisi iste magni praesentium nesciunt cumque veniam cum, ullam aliquam culpa itaque et accusantium. Delectus, blanditiis voluptatem reiciendis aperiam omnis sapiente expedita itaque nesciunt, sequi quisquam fugiat vitae repellendus perferendis dicta eveniet tenetur nam possimus cum corporis voluptas quam harum! Quia labore harum cumque officia, veniam eaque fugiat quas est repellendus, assumenda, libero illo. Consequuntur culpa error quisquam eius id! Ea, dolore at. Natus eligendi sint commodi totam quos praesentium ipsum, nemo aperiam animi repellendus magni. Id enim molestias hic ullam harum repellat, esse, magnam quis incidunt voluptates excepturi ducimus distinctio quo. Sit quibusdam labore tenetur libero! Natus et quidem aperiam dolores quas voluptatem iste hic necessitatibus consequatur sunt. Officia repellat, laudantium maxime eaque nam explicabo dignissimos dolorum accusantium mollitia nulla et exercitationem nesciunt? Repellat eveniet optio error sequi earum nisi! Ratione animi quam sit odio similique accusantium odit blanditiis dignissimos non quibusdam, dolore placeat ipsum assumenda doloremque rerum suscipit nisi eaque rem fugiat quae totam neque eveniet iure enim. Aspernatur optio velit a debitis impedit, delectus quos dolorem repellendus voluptatem reprehenderit ratione fuga officiis culpa veniam nulla neque nobis, adipisci eligendi saepe ipsa laborum! Perspiciatis nam amet, ratione nihil at eos neque dolore totam ut dignissimos explicabo. Et!</p>
            </div>

        </div>
    )
}

export default Index