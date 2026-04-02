const db = require('../src/db/postgres');

const teams = [
  { name: 'Deportivo Maza', players: ['Arboleda Angel','Sarasola Matias','Labín Juan','Gertner Genaro','Fernandez Benjamín','Moggia Lucio','Duckardt Sebastian','Risso Jeremias','Kuwalsuk Angel','Rodi Matías','Ditzel Juan','Rivero Cayetano','Alvarez Francisco','Gonzalez Fernando','Paturlane Santos','Herrera Facundo'] },
  { name: 'Atletico Quenumá', players: ['Marduel Agustín','Cotignola Francisco','Montes Maximiliano','Pescatori Felipe','Alvarez Nicolas','Ibañez Mateo','Garcia Tobias','Ibañez David','Cepeda Lucas','Boquín Nicolás','Maya Federico','Vidaurre Iñaky','Wilchez Bruno','Bermejo Rodrigo','Bornes Kevin','Cerna Cristian','Quiroga Ignacio','Perroud Federico'] },
  { name: 'Deportivo Garré', players: ['García Jordan','Avalos Enzo','Vela Lucas','Lopez Angel','Caceres Elias','Lara Joaquín','Martinez Miguel','Candia Fabricio','Rodríguez Tomás','Sanchez Juan','Calvo Tobias','Gonzalez Herberto','Sica Axel','Prienza Federico','Mascioli Benicio','Silva Nicolas'] },
  { name: 'Salazar FC', players: ['Barni Franco','Mendez Juan','Correa Jaime','Gallardo Santiago','Colagioia Matías','Ducasse Manuel','Benito Carlos','Contreras Mariano','Espinola Santiago','Mansilla Mariano','Lucas Ulises','Di Palma Enzo','Anania Franco','Caris Agustín','Prieto Gonzalo','Olaverria Valentin'] },
  { name: 'Deportivo Argentino', players: ['Reale Exequiel','Álvarez Braian','Fernandez German','Suarez Milton','Silva Alejandro','Herrera Esteban','Proetto Ignacio','González Dardo','Calabaza Franco','Silva Jonatan','Saucedo Sandro','Taborda Ignacio','Rodríguez Braian','Mac Axel','Mang Emiliano','Castillo Ignacio'] },
  { name: 'Juventud Unida', players: ['Ghinzani Federico','Álvarez Braian','Soto Richard','Souza Lautaro','Flores Nahuel','Gómez Adrián','Monteventi Rocco','Domínguez Geremías','Culasso Gerónimo','Flores Daniel','Calabaza Francisco','Correa Federico','Pérez Federico','Rosales Federico','Calabaza Franco','Briozzo Agustín','Olivares Damián','Correa Agustín'] },
  { name: 'Villa Del Parque', players: ['Luquez Joel','Horbal Matías','Bertolotti Julian','Ponce Marcos','Sosa Julian','Aquino Alejandro','Sandoval Ulises','Diego Matias','Aul Julian','Argañin Leo','Romero Martin','Gonzalez Facundo','Villarreta Carlos','Gonzalez Julian','Lucero Cristian','Marañon Fermín','Sanchez Tomaa','Calvos Matías'] },
  { name: 'El Ceibo', players: ['Argañin Juan','Puente Franco','Sanchez Lautaro','Galván Juan','Gramajo Boris','Barrera Agustin','Ometto Claudio','Cerjenko Gastón','Soto Marcelo','Di Paula Martín','Barros Maximiliano','Gonzalez Sebastian','Puente Fermín','Marroquín Vicente','Romero Maximo','Bellegia Matías','Barrientos Jeremias','Ometto Mateo'] },
  { name: 'La Gloria', players: ['Medina Gabriel','Santander Alexis','Córdoba Daniel','Martino Luciano','Luna Braian','Petti Elías','Huerta Paulo','Méndez Ciro','Aguirre Gaspar','Galeano Joaquín','Digiacomo Gabriel','Calvo Agustin','Serra Yenien','Coña Jorge','Palomeque Sebastian','Gonzalez Christian'] },
  { name: 'Jorge Newbery', players: ['Troya Lucas','Domínguez Sebastián','Peñas Ignacio','Caicedo Kevin','Cuevas Juan','Ramos Luciano','Medina Cesar','Candia Enzo','Ibañez Nicolás','Cardozo Hector','Barrios Tobias','Illesca Sebastián','Siri Ignacio','Herrero Donato','Corral Juan','Palacios Alejo','Aranda Sergio'] },
  { name: 'Union Deportiva', players: ['Paterlini Emanuel','Ávila Jeremías','Farias Alan','Martín Gonzalo','Semino Braian','Changazzo Braian','Zárate Joaquín','Ramírez Diego','Tomasello Uriel','Zárate Cruz','Barrio Agustin','Carnero Julian','Toledo Pedro','Toribio Facundo','Villagra Carlos','Burrieza Leonel','Del Arco Matías','Pelosi Enzo'] },
  { name: 'Atlético Argentino', players: ['Ibañez Matias','Navarro Joaquín','Aro Valentín','Arenas Elias','Gonzalez Maximo','Lopez Joaquín','Hoyos Matías','Sardon José','Martín Michael','Fraile Thiago','Borregon Joaquín','Guerrero Joaquín','Toscano Julian','Enrriquez Juan','Arancivia Simon','Gúzman Roman','Moralejo Manuel','Fraile Alexis'] },
  { name: 'Union de Bonifacio', players: ['Gómez Agustín','Duarte Agustín','Feloy Juan','Borniego Felipe','Ramírez Francisco','Leiva Mariano','Martinez Uriel','Achaval Juan','Achaval Diego','Fernandez Michel','Leiva Yaco','Cepeda Felipe','Torres Julian','Achaval Gonzalo','Satarain Santiago','Gallego Alejandro'] },
  { name: 'Deportivo 17', players: ['Coña Antonio','Roman Joel','Toribio Kevin','Coña Julian','Ibañez Ezequiel','Martins Ignacio','Barrio Joaquín','Gomez Diego','Sandalich Nahuel','Benavidez Joan','Guiyelo Benjamin','Bravo Tomas','Miranda Cristian','Coña Agustín','Esteban Juan','Del Río Ricardo','Root Martín','Rodriguez Tiago'] }
];

(async () => {
  try {
    await db.query('BEGIN');
    await db.query('DELETE FROM eventos');
    console.log('Eventos eliminados.');
    await db.query('DELETE FROM jugadores');
    console.log('Jugadores eliminados.');

    const resultEquipos = await db.query('SELECT id, nombre FROM equipos');
    const equipos = resultEquipos.rows;

    const normalize = (name) => name.normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase().trim();
    const equipoMap = new Map(equipos.map((e) => [normalize(e.nombre), e.id]));

    for (const team of teams) {
      const teamKey = normalize(team.name);
      const equipoId = equipoMap.get(teamKey);
      if (!equipoId) {
        console.warn(`Equipo no encontrado en DB: ${team.name}`);
        continue;
      }
      for (const jugador of team.players) {
        await db.query(
          'INSERT INTO jugadores (nombre, posicion, equipo, goles, amarillas, rojas) VALUES ($1, $2, $3, 0, 0, 0)',
          [jugador, 'No asignada', equipoId]
        );
      }
      console.log(`Insertados ${team.players.length} jugadores en ${team.name}`);
    }

    await db.query('COMMIT');
    console.log('Importación completa.');
    process.exit(0);
  } catch (error) {
    await db.query('ROLLBACK');
    console.error('Error al importar jugadores:', error);
    process.exit(1);
  }
})();
