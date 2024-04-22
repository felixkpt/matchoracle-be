import ScrollToTop from '@/components/ScrollToTop';
import NavBar from '../Shared/Navbar/Index';
import Footer from '../Shared/Footer/Index';

interface Props {
	Component: React.ComponentType
}
const GuestLayout = ({ Component }: Props) => {
	return (
		<>
			<ScrollToTop />
			<>
				<NavBar guestMode={true} />
				<div id="layoutWrapper">
					<div id="mainContent" style={{ paddingLeft: '0' }}>
						<div className='main-content mb-4'>
							<main className='main-content-inner container-fluid mt-2 px-3 min-h-100vh'>
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
