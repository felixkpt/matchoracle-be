import ScrollToTop from '@/components/ScrollToTop';
import Footer from '../Shared/Footer/Index';
import NavBar from '../Shared/Navbar/Index';

interface Props {
	Component: React.ComponentType
}
const GuestLayout = ({ Component }: Props) => {
	return (
		<>
			<ScrollToTop />
			<>
				<NavBar sidNavHidden={true} />
				<div id="layoutWrapper">
					<div id="mainContent" className='p-4' style={{ marginLeft: '0' }}>
						<div className='main-content my-2'>
							<main className='main-content-inner container-fluid p-3 min-h-100vh'>
								<div className="container">
									<div className="row justify-content-center">
										<Component />
									</div>
								</div>
							</main>
						</div>
						<Footer />
					</div>
				</div>
			</>
		</>
	);
}

export default GuestLayout
