<?php

/* 
 *  2019 @author Claudio Souza Jr. <claudio@uerr.edu.br>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Verifica se o usuário tem permissão de acesso a determinado método.
 * 
 * @return void|boolean
 */
function system_security_permission() {
    $ci = & get_instance();
    $method_active = $ci->config->item('method_active');
    $metodos = $ci->metodos->get_all();
    $login = $ci->session->userdata('usuario')['cpf'];
    # Testa se a sessão existe;
    if ( $login !== NULL ) {
        # Se existir, recupera os dados do usuário;
        $usuario = $ci->usuarios->select($ci->db->escape($login));
        # Testa o nível de acesso do usuário;
        if ($usuario['nivel_de_acesso'] == 0) {
            # Se for 0, pode tudo;
            //echo 'Usuário administrador!';
            return TRUE;
        }
        else {
            //echo 'Usuário operador!';
            # Se não for 0, testa as permissões específicas do usuário;
            $usuarios_permissoes = $ci->usuarios_permissoes->get_all_where($usuario['id']);
            foreach($metodos as $method) {
                if ($method_active == $method['nome_do_metodo']) {
                    # Se o method for publico, deixa passar;
                    if('t' == $method['publico']){
                        //echo 'Este método é público';
                        return TRUE;
                    }
                }
            }
            if(!in_array($method_active, $usuarios_permissoes)){
                # Se o usuário não tem a permissão, redireciona;
                $msg = "Usuário {$login} ";
                $msg.= 'não tem permissão para acessar o method '.$method_active;
                $alert = ['class'=>'error', 'msg'=>$msg];
                $ci->session->set_flashdata('alert', $alert);
                $ci->logs->write_log($alert['class'],'HOOK;'.__FUNCTION__.';'.__LINE__.';'.$alert['msg']);
                redirect('dashboard');
            }
        }
    }
}