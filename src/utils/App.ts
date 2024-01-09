class App {
    static name = () => import.meta.env.VITE_APP_NAME || 'MatchOracle'
    static home = () => import.meta.env.VITE_APP_HOME || '/'
}

export default App