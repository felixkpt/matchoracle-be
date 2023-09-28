class App {
    static name = () => import.meta.env.VITE_APP_NAME || 'SwiftCRM'
    static home = () => import.meta.env.VITE_APP_HOME || '/'
}

export default App