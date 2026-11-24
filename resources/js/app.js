import './bootstrap';
import { iniciarFormularios } from './modulos/formularios';
import { iniciarKds } from './modulos/kds';
import { iniciarNavegacion } from './modulos/navegacion';
import { iniciarPos } from './modulos/pos';
import { iniciarRelojes } from './modulos/reloj';
import { iniciarTecladoPin } from './modulos/teclado-pin';

iniciarNavegacion();
iniciarFormularios();
iniciarRelojes();

document.querySelectorAll('[data-pos]').forEach(iniciarPos);
document.querySelectorAll('[data-kds]').forEach(iniciarKds);
document.querySelectorAll('[data-teclado-pin]').forEach(iniciarTecladoPin);
