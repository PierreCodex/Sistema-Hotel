import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

const FeatureList = [
  {
    title: '🏨 Gestión de Habitaciones',
    image: '/img/screenshots/Habitaciones.png',
    description: (
      <>
        Administra habitaciones, pisos, categorías, estados y tarifas.
        Control completo de disponibilidad y precios por temporada.
      </>
    ),
    link: '/docs/modules/rooms',
  },
  {
    title: '📋 Recepción',
    image: '/img/screenshots/VISTARECEPCIONES.png',
    description: (
      <>
        Check-in y check-out de huéspedes. Soporta estadías por 3 horas
        o por noche con tarifas personalizadas.
      </>
    ),
    link: '/docs/modules/reception',
  },
  {
    title: '💳 Facturación',
    image: '/img/screenshots/Facturacion.png',
    description: (
      <>
        Genera boletas y facturas automáticamente. Cálculo de montos,
        adelantos, penalidades y exportación a PDF.
      </>
    ),
    link: '/docs/modules/billing',
  },
];

function Feature({ image, title, description, link }) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center">
        <a href={link}>
          <img src={image} className={styles.featureImg} alt={title} />
        </a>
      </div>
      <div className="text--center padding-horiz--md">
        <Heading as="h3">
          <a href={link} className={styles.featureLink}>{title}</a>
        </Heading>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures() {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="text--center margin-bottom--lg">
          <Heading as="h2">Módulos Principales</Heading>
          <p>Explora la documentación de cada módulo del sistema</p>
        </div>
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
