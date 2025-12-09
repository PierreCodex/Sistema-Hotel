import clsx from 'clsx';
import Link from '@docusaurus/Link';
import useDocusaurusContext from '@docusaurus/useDocusaurusContext';
import Layout from '@theme/Layout';
import HomepageFeatures from '@site/src/components/HomepageFeatures';

import Heading from '@theme/Heading';
import styles from './index.module.css';

function HomepageHeader() {
  const { siteConfig } = useDocusaurusContext();
  return (
    <header className={clsx('hero', styles.heroBanner)}>
      <div className="container">
        <div className={styles.heroContent}>
          <div className={styles.heroText}>
            <Heading as="h1" className="hero__title">
              🏨 {siteConfig.title}
            </Heading>
            <p className="hero__subtitle">{siteConfig.tagline}</p>
            <p className={styles.heroDescription}>
              Documentación completa para el sistema de gestión hotelera.
              Incluye guías de instalación, arquitectura del sistema y
              documentación detallada de cada módulo.
            </p>
            <div className={styles.buttons}>
              <Link
                className="button button--primary button--lg"
                to="/docs/intro">
                📚 Comenzar
              </Link>
              <Link
                className="button button--secondary button--lg"
                to="/docs/getting-started/installation">
                ⚙️ Instalación
              </Link>
            </div>
          </div>
          <div className={styles.heroImage}>
            <img
              src="/img/screenshots/DASBOARD.png"
              alt="Dashboard Sistema Hotel"
              className={styles.screenshot}
            />
          </div>
        </div>
      </div>
    </header>
  );
}

export default function Home() {
  const { siteConfig } = useDocusaurusContext();
  return (
    <Layout
      title="Documentación"
      description="Documentación oficial del Sistema de Gestión Hotelera">
      <HomepageHeader />
      <main>
        <HomepageFeatures />
      </main>
    </Layout>
  );
}
