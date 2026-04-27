import { Component, EventEmitter, Input, Output } from '@angular/core';

/**
 * Component reutilitzable de diàleg de confirmació.
 *
 * Rep el missatge i la visibilitat via @Input i emet els events
 * `confirmat` i `cancelat` via @Output.
 *
 * Exemple d'ús:
 *   <app-confirm-dialog
 *     [visible]="mostrarDialog"
 *     [missatge]="'Segur que vols eliminar aquest element?'"
 *     (confirmat)="onConfirmar()"
 *     (cancelat)="onCancel()">
 *   </app-confirm-dialog>
 */
@Component({
  selector: 'app-confirm-dialog',
  standalone: true,
  template: `
    @if (visible) {
      <div class="confirm-overlay" (click)="cancelat.emit()" role="dialog" aria-modal="true" [attr.aria-label]="missatge">
        <div class="confirm-box" (click)="$event.stopPropagation()">
          <p class="confirm-message">{{ missatge }}</p>
          <div class="confirm-actions">
            <button type="button" class="btn-confirm-si" (click)="confirmat.emit()">
              {{ labelConfirmar }}
            </button>
            <button type="button" class="btn-confirm-no" (click)="cancelat.emit()">
              {{ labelCancelar }}
            </button>
          </div>
        </div>
      </div>
    }
  `,
  styles: [`
    @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@700;800&display=swap');

    .confirm-overlay {
      position: fixed;
      inset: 0;
      background: rgba(10, 20, 60, 0.55);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
    }
    .confirm-box {
      background: rgba(255, 255, 255, 0.96);
      border-radius: 28px;
      border: 5px solid #FFD400;
      padding: 2rem 2.4rem;
      max-width: 360px;
      width: 90%;
      text-align: center;
      box-shadow: 0 28px 62px rgba(0, 0, 0, 0.28), inset 0 1px 0 rgba(255,255,255,0.7);
    }
    .confirm-message {
      margin: 0 0 1.6rem;
      font-family: Inter, system-ui, sans-serif;
      font-size: 1rem;
      font-weight: 700;
      color: #1E2A78;
    }
    .confirm-actions {
      display: flex;
      gap: 0.9rem;
      justify-content: center;
    }
    .btn-confirm-si,
    .btn-confirm-no {
      padding: 9px 22px;
      border-radius: 999px;
      border: 3px solid rgba(0,0,0,0.82);
      font-family: Inter, system-ui, sans-serif;
      font-weight: 800;
      font-size: 0.85rem;
      letter-spacing: 0.03em;
      cursor: pointer;
      transition: transform 0.12s, filter 0.12s;
      white-space: nowrap;
    }
    .btn-confirm-si {
      background: #e74c3c;
      color: #fff;
    }
    .btn-confirm-no {
      background: #eee;
      color: #333;
    }
    .btn-confirm-si:hover { transform: translateY(-1px); filter: saturate(1.1); }
    .btn-confirm-no:hover { transform: translateY(-1px); filter: saturate(1.1); }
    .btn-confirm-si:active, .btn-confirm-no:active { transform: translateY(0); }
  `]
})
export class ConfirmDialogComponent {
  /** Missatge a mostrar dins el diàleg */
  @Input() missatge = 'Estàs segur?';

  /** Text del botó de confirmació */
  @Input() labelConfirmar = 'Sí';

  /** Text del botó de cancel·lació */
  @Input() labelCancelar = 'No';

  /** Controla si el diàleg és visible */
  @Input() visible = false;

  /** Emet quan l'usuari confirma l'acció */
  @Output() confirmat = new EventEmitter<void>();

  /** Emet quan l'usuari cancel·la l'acció */
  @Output() cancelat = new EventEmitter<void>();
}
